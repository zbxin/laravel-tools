<?php

namespace ZhiEq\Events;

abstract class ValidatorEvent extends Event implements EventValidatorInterface
{
    use EventValidatorTrait;
}
