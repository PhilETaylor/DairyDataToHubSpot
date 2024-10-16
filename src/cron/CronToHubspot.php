<?php
/*
 * @author    Phil E. Taylor <phil@phil-taylor.com>
 * @copyright Copyright (C) 2024 Red Evolution Limited.
 * @license   GPL
 */

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use HubSpot\Client\Crm\Contacts\Model\Filter;
use HubSpot\Client\Crm\Contacts\Model\FilterGroup;
use HubSpot\Client\Crm\Contacts\Model\PublicObjectSearchRequest;
use HubSpot\Client\Crm\Contacts\Model\SimplePublicObject;
use HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput;
use HubSpot\Discovery\Discovery;
use HubSpot\Factory;
use League\Csv\Reader;
use RedEvo\Halite;
use RedEvo\LogHander;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../../.env');

class CronToHubspot
{
    private Discovery $hubspot;

    public function __construct(
        private readonly LogHander $log,
        private readonly Halite    $halite
    ) {
        $filesToProcess = glob(__DIR__ . '/../../data/*');
        $this->initHubspot();

        $this->log->debug(\sprintf('Found %s files to process', is_countable($filesToProcess) ? \count($filesToProcess) : '0'));

        foreach ($filesToProcess as $file) {
            $this->log->debug('Processing file: ' . $file);
            $this->processFile($file);
        }
    }

    private function initHubspot(): void
    {
        if ((\array_key_exists('DEV', $_ENV) && $_ENV['DEV'] === 'true')) {
            $client = new Client([
                RequestOptions::PROXY  => [
                    'http'  => 'http://192.168.1.118:8888',
                    'https' => 'http://192.168.1.118:8888',
                ],
                RequestOptions::VERIFY => __DIR__ . '/../../dev/ca.pem', # Force local proxy SSL certificate validation
            ]);
        } else {
            $client = new Client();
        }

        $this->hubspot = Factory::createWithAccessToken($_ENV['HUBSPOT_ACCESS_TOKEN'], $client);
    }

    private function processFile(mixed $file): void
    {
        if (str_contains((string) $file, '.enc')) {
            $this->log->debug('Attempting To Decrypt file: ' . $file);
            $this->halite->decrypt($file);
        } else {
            $this->log->debug('Skipping Decrypt file: ' . $file);
        }

        $this->log->debug('Loading file data from file: ' . $file);
        $csv = Reader::createFromPath(str_replace('.enc', '', $file), 'r');

        $count = $csv->count();
        $this->log->debug(\sprintf('There are %s records to process', $count));

        $records = $csv->getRecords();

        $i = 0;
        foreach ($records as $record) {
            echo $i++ . ' = ' . number_format($i / $count, 4) . \PHP_EOL;
            $this->processRecord($record);
        }

        // delete the file
        unlink($file);
    }

    private function processRecord(array $record): void
    {
        // double emails
        if (str_contains((string) $record[9], ',')) {
            return;
        }

        $data = [
            'dairydata_id' => $record[0],
            'salutation'   => $record[1],
            'firstname'    => $record[2],
            'lastname'     => $record[3],
            // 4 is unknown
            'address'      => $record[5],
            'zip'          => $record[6],
            'phone'        => $record[7],
            'mobilephone'  => $record[8],
            'email'        => trim(strtolower((string) $record[9])),
            //10 unknown
            //11 unknown
            //12 unknown
        ];

        $results = $this->searchContactByProperty($record[0]);
        if ($results->getTotal() === 0) {
            $this->createContact($record, $data);
        } else {
            $this->updateContact($results->getResults()[0], $data);
        }

        usleep(100000);
    }

    private function createContact(array $record, array $data): void
    {
        $this->log->debug('Creating a new record for DairyDiary id: ' . $record[0]);

        $contactInput = new SimplePublicObjectInput();
        $contactInput->setProperties($data);

        try {
            $this->hubspot->crm()
                ->contacts()
                ->basicApi()
                ->create($contactInput);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), '409 Conflict')) {
                $record = $this->searchContactByProperty($record[9], 'email');
                if ($record->getTotal() !== 0) {
                    $this->updateContact($record->getResults()[0], $data);
                }
            }
        }
    }

    private function updateContact(SimplePublicObject $record, array $newData): void
    {
        $this->log->debug('Updating an existing record for DairyDiary id: ' . $record[0]);

        $oldRecord = $record->getProperties();

        $newRecord = array_merge($oldRecord, $newData);

        unset($newRecord['hs_object_id'], $newRecord['createdate'], $newRecord['lastmodifieddate']);
        unset($oldRecord['hs_object_id'], $oldRecord['createdate'], $oldRecord['lastmodifieddate']);

        // Filter out null values
        $newRecord = array_filter($newRecord, fn ($value) => ! \is_null($value) && $value !== '');
        $oldRecord = array_filter($oldRecord, fn ($value) => ! \is_null($value) && $value !== '');

        // rearrange the arrays to ensure we can compare them
        ksort($newRecord);
        ksort($oldRecord);

        if ($newRecord === $oldRecord) {
            $this->log->debug('Nothing to update for existing record for DairyDiary id: ' . $record[0]);

            return;
        }

        // clone to a new Input object - important.
        $newProperties = new SimplePublicObjectInput();
        $newProperties->setProperties($newRecord);

        try {
            $this->hubspot->crm()
                ->contacts()
                ->basicApi()
                ->update($record->getId(), $newProperties);
        } catch (Exception $e) {
            $this->log->critical('EXCEPTION: ' . $e->getMessage() . \PHP_EOL . 'STACK TRACE:' . \PHP_EOL . $e->getTraceAsString());
            echo 'EXCEPTION: ' . $e->getMessage() . \PHP_EOL;
        }
    }

    private function searchContactByProperty(string $id, string $property = 'dairydata_id')
    {
        $filter = new Filter();
        $filter
            ->setOperator('EQ')
            ->setPropertyName($property)
            ->setValue($id);

        $filterGroup = new FilterGroup();
        $filterGroup->setFilters([$filter]);

        $searchRequest = new PublicObjectSearchRequest();
        $searchRequest->setFilterGroups([$filterGroup]);

        // Get specific properties
        $searchRequest->setProperties([
            'dairydata_id',
            'salutation',
            'firstname',
            'lastname',
            'address',
            'zip',
            'phone',
            'mobilephone',
            'email',
        ]);

        return $this->hubspot->crm()
            ->contacts()
            ->searchApi()
            ->doSearch($searchRequest);
    }
}

new cronToHubspot(new LogHander(), new Halite());
