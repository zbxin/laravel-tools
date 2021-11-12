<?php

namespace Zbxin\Events;

abstract class ValidatorEvent extends Event implements EventValidatorInterface
{
    use EventValidatorTrait;
}
