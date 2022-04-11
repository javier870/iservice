<?php

namespace App\Controller;

use App\Entity\Vehicle;
use App\Entity\Paginator;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter as ORMParameter;
use OpenApi\Annotations as OA;
use App\OpenApi\Annotations as AOA;


/**
 * @Route("/api/vehicles")
 */
class VehicleController extends AbstractController
{
    /**
     * Paginated, sortable, and searchable vehicles list.
     *
     * @Route("/", name="api_vehicles_list", methods={"GET"})
     * @OA\Parameter(name="page", in="query", required=false, description="Page number",
     *         @OA\Schema(type="integer", example="1"))
     * @OA\Parameter(name="max", in="query", required=false, description="Maximum number of vehicles per page",
     *         @OA\Schema(type="integer", example="20"))
     * @OA\Parameter(name="order", in="query", required=false, description="Field to be ordered.",
     *         @OA\Schema(type="string", example="make"))
     * @OA\Parameter(name="sort", in="query", required=false, description="Sort direction(ASC or DESC).",
     *         @OA\Schema(type="string", example="ASC"))
     * @OA\Parameter(name="search", in="query", required=false, description="Search criteria to find vehicles by make, model, or vin.",
     *         @OA\Schema(type="string", example=""))
     * @OA\Response(
     *     response=200,
     *     description="Returns the Vehicles list",
     *     @OA\JsonContent(
     *          type="array",
     *          @OA\Items(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref=@Model(type=Vehicle::class, groups={"list"}))
     *              ),
     *              @OA\Property(
     *                  property="errors",
     *                  type="array",
     *                  @OA\Items()
     *              )
     *        )
     *     )
     *)
     */
    public function vehicles(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator): JsonResponse
    {
        //pagination, filters, and sort data
        $paginator = new Paginator();
        $paginator->setPage($request->query->get('page', "1"));
        $paginator->setMax($request->query->get('max', "20"));
        $paginator->setOrder($request->query->get('order', 'dateAdded'));
        $paginator->setSort($request->query->get('sort', 'ASC'));
        $paginator->setSearch($request->query->get('search'));

        //validating pagination data
        $errors = $validator->validate($paginator);
        if (count($errors) > 0) {
            $indexed = [];
            foreach ($errors as $error) {
                $indexed[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $this->getResponseFormat("Errors found!", [], $indexed);
        }

        //retrieve vehicles data from DB
        $entityManager = $doctrine->getManager();
        $vehicles = $entityManager->getRepository(Vehicle::class);

        // build the query for the doctrine paginator
        $query = $vehicles->createQueryBuilder('v')
            ->where("v.type= :type AND v.deleted <> 1")
            ->orderBy('v.' . $paginator->getOrder(), $paginator->getSort());

        $ArrayCollection = [new ORMParameter('type', $this->getParameter('app.paramname'))];

        if ($paginator->getSearch()) {
            //prepare search for query
            $query = $query->andWhere("v.make LIKE :search OR v.model LIKE :search OR v.vin LIKE :search");

            $ArrayCollection[] = new ORMParameter('search', '%' . $paginator->getSearch() . '%');

        }
        $query = $query->setParameters(new ArrayCollection($ArrayCollection));

        //doctrine Paginator
        $doctrine_paginator = new DoctrinePaginator($query);

        //total vehicles
        $total = count($doctrine_paginator);

        $max = (int)$paginator->getMax();
        // now get one page's items:
        $doctrine_paginator
            ->getQuery()
            ->setFirstResult($max * ($paginator->getPage() - 1)) // set the offset
            ->setMaxResults($max); // set the limit

        //select needed data for all vehicles
        $response = [
            'pages' => ceil($total / $max),//total pages
            'total' => $total,//total vehicles
            'vehicles' => [],//vehicles
        ];
        foreach ($doctrine_paginator as $vehicle) {
            $response ['vehicles'][] = [
                'id' => $vehicle->getId(),
                'dateAdded' => $vehicle->getDateAdded(),
                'msrp' => $vehicle->getMsrp(),
                'year' => $vehicle->getYear(),
                'make' => $vehicle->getMake(),
                'model' => $vehicle->getModel(),
                'miles' => $vehicle->getMiles(),
                'vin' => $vehicle->getVin()
            ];
        }

        return $this->getResponseFormat("success", $response, []);
    }

    /**
     * Shows an existing vehicle info.
     *
     * @Route("/show/{id}", name="api_vehicles_show", methods={"GET"})
     *
     * @OA\Parameter(name="id", in="path", required=true, description="The unique identifier of the vehicle.",
     *         @OA\Schema(type="integer", example="538"))
     *
     * @OA\Response(
     *     response=200,
     *     description="Returns success or error found",
     *     @OA\JsonContent(
     *          type="array",
     *          @OA\Items(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="object", ref=@Model(type=Vehicle::class, groups={"show"})),
     *              @OA\Property(
     *                  property="errors",
     *                  type="array",
     *                  @OA\Items()
     *              )
     *        )
     *     )
     *)
     */
    public function show($id, ManagerRegistry $doctrine): JsonResponse
    {
        //retrieve the vehicle data from DB
        $entityManager = $doctrine->getManager();
        $vehicle = $entityManager->getRepository(Vehicle::class)->find($id);

        if (!$vehicle) {
            return $this->getResponseFormat("Errors found!", [], ['id' => 'No product found for id ' . $id]);
        }

        //verify type
        if ($vehicle->getType() != $this->getParameter('app.paramname')) {
            throw $this->createNotFoundException(
                'No product found for id ' . $id
            );
        }

        //select needed data
        $data['date_dded'] = $vehicle->getDateAdded();
        $data['msrp'] = $vehicle->getMsrp();
        $data['year'] = $vehicle->getYear();
        $data['make'] = $vehicle->getMake();
        $data['model'] = $vehicle->getModel();
        $data['miles'] = $vehicle->getMiles();
        $data['vin'] = $vehicle->getVin();
        $data['deleted'] = $vehicle->isDeleted();

        return $this->getResponseFormat("success", $data, []);
    }

    /**
     * Deletes an existing vehicle from the system.
     *
     * @Route("/delete/{id}", name="api_vehicles_delete", methods={"DELETE"})
     * @OA\Parameter(name="id", in="path", required=true, description="The unique identifier of the vehicle.",
     *         @OA\Schema(type="integer", example="538"))
     *
     * @OA\Response(
     *     response=200,
     *     description="Returns success or error found",
     *     @OA\JsonContent(
     *          type="array",
     *          @OA\Items(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object"
     *              ),
     *              @OA\Property(
     *                  property="errors",
     *                  type="array",
     *                  @OA\Items()
     *              )
     *        )
     *     )
     *)
     */
    public function delete($id, ManagerRegistry $doctrine): JsonResponse
    {
        //retrieve the vehicle data from DB
        $entityManager = $doctrine->getManager();
        $vehicle = $entityManager->getRepository(Vehicle::class)->find($id);

        if (!$vehicle) {
            return $this->getResponseFormat("Errors found!", [], ['id' => 'No product found for id ' . $id]);
        }

        //remove
        $entityManager->remove($vehicle);
        $entityManager->flush();

        return $this->getResponseFormat("success", [], []);
    }

    /**
     * Adds a vehicle to the system.
     *
     * @Route("/create", name="api_vehicles_create", methods={"POST"})
     * @AOA\Parameter(name="type", in="formData", style="simple", required=true, description="Only new/used options are allowed.",
     *         @OA\Schema(type="string", example="new"))
     * @AOA\Parameter(name="msrp", in="formData", style="simple", required=true, description="Manufacturer Suggested Retail Price must be decimal(20,2), up to 2 decimal places.",
     *         @OA\Schema(type="number", example="8500.99"))
     * @AOA\Parameter(name="year", in="formData", style="simple", required=true, description="Built year",
     *         @OA\Schema(type="integer", example="2022"))
     * @AOA\Parameter(name="make", in="formData", style="simple", required=true, description="",
     *         @OA\Schema(type="string", example="Ford"))
     * @AOA\Parameter(name="model", in="formData", style="simple", required=true, description="",
     *         @OA\Schema(type="string", example="F150"))
     * @AOA\Parameter(name="miles", in="formData", style="simple", required=true, description="Miles in the Odometer",
     *         @OA\Schema(type="integer", example="35000"))
     * @AOA\Parameter(name="vin", in="formData", style="simple", required=true, description="Vehicle Identification Number must be unique.",
     *         @OA\Schema(type="string", example="1FTEW1C58NKD33222"))
     * @AOA\Parameter(name="deleted", in="formData", style="simple", required=true, description="Makes the vheicle (dis)appear from the list.",
     *         @OA\Schema(type="boolean", example="false"))
     *
     * @OA\Response(
     *     response=200,
     *     description="Returns the vehicle id or error found",
     *     @OA\JsonContent(
     *          type="array",
     *          @OA\Items(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example="538"),
     *              ),
     *              @OA\Property(
     *                  property="errors",
     *                  type="array",
     *                  @OA\Items()
     *              )
     *        )
     *     )
     *)
     */
    public function create(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator): JsonResponse
    {
        $form = $request->request->all();

        if (count($form) == 0) {
            return $this->getResponseFormat("Errors found!", [], ['data' => ['No data sent to update.']]);
        }

        //new vehicle initialization
        $vehicle = new Vehicle();
        $vehicle->setDateAdded(new DateTime());

        //validate data
        $errors = $this->validateVehicle($form, $vehicle, $validator);

        if (count($errors) > 0) {
            return $this->getResponseFormat("Errors found!", [], $errors);
        }

        //save into the DB
        $entityManager = $doctrine->getManager();
        $entityManager->persist($vehicle);
        $entityManager->flush();

        return $this->getResponseFormat("success", ['id' => $vehicle->getId()], []);
    }

    /**
     * Updates an existing vehicle info.
     *
     * @Route("/update/{id}", name="api_vehicles_update", methods={"PATCH"})
     * @OA\Parameter(name="id", in="path", required=true, description="The unique identifier of the vehicle.",
     *         @OA\Schema(type="integer", example="538"))
     *
     * @AOA\Parameter(name="type", in="formData", style="simple", required=false, description="Only new/used options are allowed.",
     *         @OA\Schema(type="string", example="new"))
     * @AOA\Parameter(name="msrp", in="formData", style="simple", required=false, description="Manufacturer Suggested Retail Price must be decimal(20,2), up to 2 decimal places.",
     *         @OA\Schema(type="number", example="8500.99"))
     * @AOA\Parameter(name="year", in="formData", style="simple", required=false, description="Built year",
     *         @OA\Schema(type="integer", example="2022"))
     * @AOA\Parameter(name="make", in="formData", style="simple", required=false, description="",
     *         @OA\Schema(type="string", example="Ford"))
     * @AOA\Parameter(name="model", in="formData", style="simple", required=false, description="",
     *         @OA\Schema(type="string", example="F150"))
     * @AOA\Parameter(name="miles", in="formData", style="simple", required=false, description="Miles in the Odometer",
     *         @OA\Schema(type="integer", example="35000"))
     * @AOA\Parameter(name="vin", in="formData", style="simple", required=false, description="Vehicle Identification Number must be unique.",
     *         @OA\Schema(type="string", example="1FTEW1C58NKD33222"))
     * @AOA\Parameter(name="deleted", in="formData", style="simple", required=false, description="Makes the vheicle (dis)appear from the list.",
     *         @OA\Schema(type="boolean", example="false"))
     *
     * @OA\Response(
     *     response=200,
     *     description="Returns success or error found",
     *     @OA\JsonContent(
     *          type="array",
     *          @OA\Items(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object"
     *              ),
     *              @OA\Property(
     *                  property="errors",
     *                  type="array",
     *                  @OA\Items()
     *              )
     *        )
     *     )
     *)
     */
    public function update(Request $request, int $id, ManagerRegistry $doctrine, ValidatorInterface $validator): JsonResponse
    {
        //retrieve the vehicle data from DB
        $entityManager = $doctrine->getManager();
        $vehicle = $entityManager->getRepository(Vehicle::class)->find($id);

        if (!$vehicle) {
            return $this->getResponseFormat("Errors found!", [], ['id' => ['No product found for id ' . $id]]);
        }

        $form = $this->getDataFromPatchContent($request->getContent());

        if (count($form) == 0) {
            return $this->getResponseFormat("Errors found!", [], ['data' => ['No data sent to update.']]);
        }

        //validate data
        $errors = $this->validateVehicle($form, $vehicle, $validator);

        if (count($errors) > 0) {
            return $this->getResponseFormat("Errors found!", [], $errors);
        }

        //save into the DB
        $entityManager->flush();

        return $this->getResponseFormat("success", [], []);
    }

    /**
     * @param $form
     * @param $vehicle
     * @param ValidatorInterface $validator
     * @return array
     */
    private function validateVehicle($form, $vehicle, ValidatorInterface $validator): array
    {
        if (isset($form['type']))
            $vehicle->setType($form['type']);
        if (isset($form['msrp']))
            $vehicle->setMsrp($form['msrp']);
        if (isset($form['year']))
            $vehicle->setYear($form['year']);
        if (isset($form['make']))
            $vehicle->setMake($form['make']);
        if (isset($form['model']))
            $vehicle->setModel($form['model']);
        if (isset($form['miles']))
            $vehicle->setMiles($form['miles']);
        if (isset($form['vin']))
            $vehicle->setVin($form['vin']);
        if (isset($form['deleted']))
            $vehicle->setDeleted($form['deleted']);

        //validating following Entity rules
        $errors = $validator->validate($vehicle);
        if (count($errors) > 0) {
            $indexed = [];
            foreach ($errors as $error) {
                $indexed[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $indexed;
        }

        //convert deleted to 0 or 1
        if (isset($form['deleted']))
            $vehicle->setDeleted(in_array($form['deleted'], ["true", "t", "1"]));
        return [];
    }

    /**
     * @param string $message
     * @param array $data
     * @param array $errors
     * @return JsonResponse
     */
    private function getResponseFormat(string $message, array $data, array $errors): JsonResponse
    {
        return $this->json([
            'message' => $message,
            'data' => $data,
            'errors' => $errors
        ]);
    }

    /**
     * @param string $data
     * @return array
     *
     * Filter form-data from a PATCH request content(string)
     */
    private function getDataFromPatchContent(string $data): array
    {
        $data = explode('name="', $data);
        unset($data[0]);
        $clean = [];
        foreach ($data as $field) {
            $temp = explode("\n",
                str_replace('"', '',
                    str_replace("\r", '', $field)));
            $clean[$temp[0]] = $temp[2];
        }
        return $clean;
    }
}