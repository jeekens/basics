<?php declare(strict_types=1);


namespace Jeekens\Basics\Spl;


interface Arrayable
{

    /**
     * @return array
     */
    public function toArray(): array;

}