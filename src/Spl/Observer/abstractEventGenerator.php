<?php declare(strict_types=1);


namespace Jeekens\Basics\Spl\Observer;


abstract class abstractEventGenerator implements EventGenerator
{

    /**
     * @var callable[]
     */
    protected $observers;

    /**
     * 添加监听者
     *
     * @param string $event
     * @param callable $callable
     */
    public function addObserver(string $event, $callable)
    {
        $this->observers[$event][] = $callable;
    }

    /**
     * 事件通知
     *
     * @param string $event
     * @param null $eventData
     */
    public function notify(string $event, $eventData = null)
    {
        $observers = $this->observers[$event] ?? [];

        foreach($observers as $observer)
        {
            if (empty($eventData)) {
                call($observer);
            } else {
                call($observer, ... to_array($eventData, false));
            }
        }
    }

}