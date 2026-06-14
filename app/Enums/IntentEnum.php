<?php

namespace App\Enums;

enum IntentEnum: string
{
    case REGISTER_PATIENT = 'REGISTER_PATIENT';
    case CREATE_APPOINTMENT = 'CREATE_APPOINTMENT';
    case GET_SERVICES = 'GET_SERVICES';
    case GET_PROFESSIONALS = 'GET_PROFESSIONALS';
    case GET_SEDES = 'GET_SEDES';
    case FAQ = 'FAQ';
    case UNKNOWN = 'UNKNOWN';
}
