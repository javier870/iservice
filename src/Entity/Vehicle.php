<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;
use OpenApi\Annotations as OA;

/**
 * @ORM\Table(name="vehicles")
 * @ORM\Entity
 * @UniqueEntity("vin")
 */
class Vehicle
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @OA\Property(description="The unique identifier of the vehicle.")
     * @Groups({"list"})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     * @OA\Property(type="string", format="date-time", description="The date and time a vehicle was added.", example="2004-03-11 01:59:39 CST")
     * @Groups({"show", "list"})
     */
    private $dateAdded;

    /**
     * @ORM\Column(type="string", length=4)
     * @Assert\NotBlank
     * @Assert\Choice(
     *     choices = {"new", "used"},
     *     message = "Only new/used options are allowed."
     * )
     * @OA\Property(type="string", example="new")
     */
    private string $type;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=2)
     * @Assert\NotBlank
     * @Assert\Regex(
     *     pattern = "/^\d{1,18}(\.\d{1,2}){0,1}$/i",
     *     htmlPattern = "^\d{1,18}(\.\d{1,2}){0,1}$",
     *     message="MSRP must be decimal(20,2), up to 2 decimal places.")
     * @OA\Property(type="number", description="decimal, precision=20, scale=2", example="8500.99")
     * @Groups({"show", "list"})
     */
    private $msrp;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     * @Assert\Type(type="digit")
     * @CustomAssert\VehicleYearRange
     * @OA\Property(type="integer", example="2022", description="Built year")
     * @Groups({"show", "list"})
     */
    private $year;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @OA\Property(type="string", example="Ford")
     * @Groups({"show", "list"})
     */
    private $make;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @OA\Property(type="string", example="F150")
     * @Groups({"show", "list"})
     */
    private $model;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     * @Assert\Type(type="digit")
     * @OA\Property(type="integer", example="35000")
     * @Groups({"show", "list"})
     */
    private $miles;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank
     * @Assert\Regex(
     *     pattern = "/^[a-z0-9]+$/i",
     *     htmlPattern = "^[a-zA-Z0-9]+$",
     *     message="VIN must contain only letter and numbers")
     * @OA\Property(type="string", description="Must be unique.", example="1FTEW1C58NKD33222")
     * @Groups({"show", "list"})
     */
    private $vin;

    /**
     * @ORM\Column(type="boolean", options={"default":0})
     * @Assert\Choice(
     *     choices = {"true", "false", "1", "0", "t", "f", true, false},
     *     message = "Only true/false/t/f/0/1 options are allowed.")
     * @OA\Property(type="boolean", description="Makes the vheicle (dis)appear from the list.", example="false")
     * @Groups({"show"})
     */
    private $deleted;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDateAdded(): string
    {
        return $this->dateAdded->format('Y-m-d H:i:s T');
    }

    /**
     * @param $dateAdded
     * @return Vehicle
     */
    public function setDateAdded($dateAdded): Vehicle
    {
        $this->dateAdded = $dateAdded;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     * @return Vehicle
     */
    public function setType($type): Vehicle
    {
        $this->type = $type;
        return $this;
    }

    public function getMsrp()
    {
        return $this->msrp;
    }

    /**
     * @param $msrp
     * @return Vehicle
     */
    public function setMsrp($msrp): Vehicle
    {
        $this->msrp = $msrp;
        return $this;
    }

    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param $year
     * @return Vehicle
     */
    public function setYear($year): Vehicle
    {
        $this->year = $year;
        return $this;
    }

    public function getMake()
    {
        return $this->make;
    }

    /**
     * @param $make
     * @return Vehicle
     */
    public function setMake($make): Vehicle
    {
        $this->make = $make;
        return $this;
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param $model
     * @return Vehicle
     */
    public function setModel($model): Vehicle
    {
        $this->model = $model;
        return $this;
    }

    public function getMiles()
    {
        return $this->miles;
    }

    /**
     * @param $miles
     * @return Vehicle
     */
    public function setMiles($miles): Vehicle
    {
        $this->miles = $miles;
        return $this;
    }

    public function getVin()
    {
        return $this->vin;
    }

    /**
     * @param $vin
     * @return Vehicle
     */
    public function setVin($vin): Vehicle
    {
        $this->vin = $vin;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @param $deleted
     * @return Vehicle
     */
    public function setDeleted($deleted): Vehicle
    {
        $this->deleted = $deleted;
        return $this;
    }
}
