<?php
declare(strict_types=1);

namespace App\Event;

use ArrayObject;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;

class AppEvents implements EventListenerInterface
{
    /**
     * Return implemented events.
     *
     * @return array<string, mixed>
     */
    public function implementedEvents(): array
    {
        return [
            'Model.beforeMarshal' => 'marshalDuration',
        ];
    }

    /**
     * Before post data is converted to entity.
     *
     * @param \Cake\Event\Event $event The event object.
     * @param \ArrayObject $data Post data.
     * @param \ArrayObject $options Additional options from controller.
     * @return void
     */
    public function marshalDuration(Event $event, ArrayObject $data, ArrayObject $options)
    {
        foreach ($data as $fieldName => $fieldValue) {
            if (is_array($fieldValue) && !empty($fieldValue['duration'])) {
                $data[$fieldName] = (int)$data[$fieldName]['hours'] * 3600 + (int)$data[$fieldName]['minutes'] * 60;
            }
        }
    }
}
