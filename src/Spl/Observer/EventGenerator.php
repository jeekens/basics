<?php


namespace Jeekens\Basics\Spl\Observer;


interface EventGenerator
{

    public function addObserver(string $event, $callable);

    public function notify(string $event, $eventData = null);

}