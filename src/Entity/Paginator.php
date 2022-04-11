<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Paginator
{
    /**
     * @Assert\NotBlank
     * @Assert\Type(type="digit")
     * @Assert\Positive
     */
    private $page;

    /**
     * @Assert\NotBlank
     * @Assert\Type(type="digit")
     * @Assert\Positive
     */
    private $max;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(
     *     choices = {"id","dateAdded","msrp", "year", "make", "model", "miles", "vin"},
     *     message = "Only id/dateAdded/msrp/year/make/model/miles/vin options are allowed."
     * )
     */
    private $order;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(
     *     choices = {"ASC", "DESC", "asc", "desc"},
     *     message = "Only ASC/DESC/asc/desc options are allowed."
     * )
     */
    private $sort;

    /**
     * @Assert\Type(type="string")
     */
    private $search;

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param mixed $page
     * @return Paginator
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param mixed $max
     * @return Paginator
     */
    public function setMax($max)
    {
        $this->max = $max;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     * @return Paginator
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSort()
    {
        return strtoupper($this->sort);
    }

    /**
     * @param mixed $sort
     * @return Paginator
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @param mixed $search
     * @return Paginator
     */
    public function setSearch($search)
    {
        $this->search = $search;
        return $this;
    }
}