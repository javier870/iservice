<?php

namespace App\Tests\Service;

use App\Entity\Vehicle;
use App\Factory\VehicleFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class VehicleTest extends WebTestCase
{
    // reset DB before each test
    //use ResetDatabase;
    use Factories;

    const required_fields = ["type", "msrp", "year", "make", "model", "miles", "vin", "deleted"];

    //http API tests

    public function test_a_vehicle_can_be_added()
    {
        $data = self::data();

        //insert vehicle
        self::ensureKernelShutdown();
        $kernel_browser = self::createClient();
        $kernel_browser->request('POST', '/api/vehicles/create', $data);
        self::assertResponseIsSuccessful();
        $response = json_decode($kernel_browser->getResponse()->getContent(), true);
        if (count($response['errors']) > 0) {
            var_dump($response['errors'], $data);
            die;
        }
        self::assertEquals('success', $response['message']);
        //retrieve vehicle inserted
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $vehicle = $entityManager->getRepository(Vehicle::class)->find($response['data']['id']);
        self::assertEquals($data['model'], $vehicle->getModel());
        self::assertEquals($data['make'], $vehicle->getMake());
        self::assertEquals($data['vin'], $vehicle->getVin());
    }

    public function test_fields_are_required_adding()
    {
        $data = self::data(false);//empty

        //insert empty vehicle
        self::ensureKernelShutdown();
        $kernel_browser = self::createClient();
        $kernel_browser->request('POST', '/api/vehicles/create', $data);
        self::assertResponseIsSuccessful();
        $response = json_decode($kernel_browser->getResponse()->getContent(), true);
        self::assertGreaterThanOrEqual(count($response['errors']), count(self::required_fields));
        //one error per field
        foreach (self::required_fields as $field) {
            self::assertTrue(isset($response['errors'][$field]));
        }
    }

    public function test_a_vehicle_can_be_updated()
    {
        //insert vehicle
        $new_vehicle = VehicleFactory::createOne();

        $update_data = [
            'make' => 'test_make',
            'model' => 'test_model'
        ];

        self::ensureKernelShutdown();
        $kernel_browser = self::createClient();
        $kernel_browser->request('PATCH', '/api/vehicles/update/' . $new_vehicle->getId(), [], [], [], '----------------------------272530620526223724823204
Content-Disposition: form-data; name="make"

' . $update_data['make'] . '
----------------------------272530620526223724823204
Content-Disposition: form-data; name="model"

' . $update_data['model'] . '
----------------------------272530620526223724823204--');
        self::assertResponseIsSuccessful();
        $response = json_decode($kernel_browser->getResponse()->getContent(), true);
        if (count($response['errors']) > 0) {
            var_dump($response['errors'],$new_vehicle);
            die;
        }
        self::assertEquals('success', $response['message']);

        //retrieve vehicle inserted
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $vehicle = $entityManager->getRepository(Vehicle::class)->find($new_vehicle->getId());
        self::assertEquals($update_data['model'], $vehicle->getModel());
        self::assertEquals($update_data['make'], $vehicle->getMake());
    }

    public function test_fields_are_required_updating()
    {
        //insert vehicle
        $new_vehicle = VehicleFactory::createOne();

        //update with empty data
        $update_data = [
            'make' => '',
            'model' => ''
        ];

        self::ensureKernelShutdown();
        $kernel_browser = self::createClient();
        $kernel_browser->request('PATCH', '/api/vehicles/update/' . $new_vehicle->getId(), [], [], [], '----------------------------272530620526223724823204
Content-Disposition: form-data; name="make"

' . $update_data['make'] . '
----------------------------272530620526223724823204
Content-Disposition: form-data; name="model"

' . $update_data['model'] . '
----------------------------272530620526223724823204--');
        self::assertResponseIsSuccessful();
        $response = json_decode($kernel_browser->getResponse()->getContent(), true);
        self::assertGreaterThanOrEqual(count($response['errors']), count($update_data));
        //one error per field
        foreach (array_keys($update_data) as $field) {
            self::assertTrue(isset($response['errors'][$field]));
        }
    }

    private function data(bool $with_data = true): array
    {
        $vehicle = VehicleFactory::new()->withoutPersisting()->create()->object();

        return $with_data ? [
            'type' => $vehicle->getType(),
            'msrp' => $vehicle->getMsrp(),
            'year' => $vehicle->getYear(),
            'make' => $vehicle->getMake(),
            'model' => $vehicle->getModel(),
            'miles' => $vehicle->getMiles(),
            'vin' => $vehicle->getVin(),
            'deleted' => $vehicle->isDeleted() ? 1 : 0,
        ] : [
            'type' => "",
            'msrp' => "",
            'year' => "",
            'make' => "",
            'model' => "",
            'miles' => "",
            'vin' => "",
            'deleted' => "",
        ];
    }

    public function test_vehicles_can_be_listed()
    {
        //insert vehicle
        VehicleFactory::createOne();

        //retrieve vehicles
        self::ensureKernelShutdown();
        $kernel_browser = self::createClient();
        $kernel_browser->request('GET', '/api/vehicles/');
        self::assertResponseIsSuccessful();
        $response = json_decode($kernel_browser->getResponse()->getContent(), true);
        self::assertEquals('success', $response['message']);
        self::assertGreaterThanOrEqual(0, $response['data']['vehicles']);
    }

    public function test_a_vehicle_can_be_shown()
    {
        //insert vehicle
        $new_vehicle = VehicleFactory::createOne(['type' => $_ENV["VEHICLE_TYPE"]]);

        //retrieve vehicle
        self::ensureKernelShutdown();
        $kernel_browser = self::createClient();
        $kernel_browser->request('GET', '/api/vehicles/show/' . $new_vehicle->getId());
        self::assertResponseIsSuccessful();
        $response = json_decode($kernel_browser->getResponse()->getContent(), true);
        self::assertEquals('success', $response['message']);
        self::assertEquals($response['data']['model'], $new_vehicle->getModel());
        self::assertEquals($response['data']['make'], $new_vehicle->getMake());
        self::assertEquals($response['data']['vin'], $new_vehicle->getVin());
    }

    public function test_a_vehicle_can_be_deleted()
    {
        //insert vehicle
        $new_vehicle = VehicleFactory::createOne();

        //delete vehicle
        self::ensureKernelShutdown();
        $kernel_browser = self::createClient();
        $kernel_browser->request('DELETE', '/api/vehicles/delete/' . $new_vehicle->getId());
        self::assertResponseIsSuccessful();
        $response = json_decode($kernel_browser->getResponse()->getContent(), true);
        self::assertEquals('success', $response['message']);

        //verify if it doesn't exist in DB
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $exception_message = "";
        try {
            $entityManager->getRepository(Vehicle::class)->find($new_vehicle->getId());
        } catch (\Exception $e) {
            $exception_message = $e->getMessage();
        }
        self::assertEquals("The object no longer exists.", $exception_message);
    }
}
