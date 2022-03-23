<?php

// Define the IRequest interface that the concrete Request class will implement
interface IRequest
{
    public function getBody(); // getBody() retrieves data from the request body. The Request class must have the implementation for this method.
}