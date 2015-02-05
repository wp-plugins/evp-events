<?php
namespace Evp\Bundle\TicketBundle\EventDispatcher;

class StepEvents {
    const FIRST_STEP_COMPLETED = 'steps.first_step_completed';
    const LAST_STEP_COMPLETED = 'steps.last_step_completed';
    const STEPS_CANCELED = 'steps.canceled';

    const NEXT_STEP = 'steps.next';
    const PREVIOUS_STEP = 'steps.previous';
}