<?php

namespace Base\Model;

use Base\Base\DB;

interface Finder
{
    function find($id);
    function findAll();

    function update(ModelObject $obj);
    function insert(ModelObject $obj);
    function delete(ModelObject $obj);
}

interface BaseModelFinder extends Finder
{
}
