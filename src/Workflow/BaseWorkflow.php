<?php

namespace App\Workflow;

abstract class BaseWorkflow
{
    public const TRANSITION_COMPLETE = 'COMPLETE';
    public const TRANSITION_PENDING = 'PENDING';
    public const TRANSITION_SUBMIT = 'SUBMIT';

    public const WORKFLOW = [];
}
