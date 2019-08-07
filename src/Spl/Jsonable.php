<?php declare(strict_types=1);


namespace Jeekens\Basics\Spl;


interface Jsonable
{

    /**
     * @param int $options
     * @param int $depth
     *
     * @return string
     */
    public function toJson(int $options = 0, int $depth = 512): string;

}