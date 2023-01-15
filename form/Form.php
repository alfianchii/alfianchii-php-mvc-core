<?php

namespace alfianchii\phpmvc\form;

use alfianchii\phpmvc\Model;

class Form
{
    /* FORM'S STRUCTURE: BEGIN, FIELD, END */
    public static function begin($action, $method)
    {
        echo sprintf("<form action='%s' method='%s'>", $action, $method);
        return new Form();
    }

    public function field(Model $model, $attribute)
    {
        return new InputField($model, $attribute);
    }

    public static function end()
    {
        echo "</form>";
    }
}
