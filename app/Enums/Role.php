<?php
namespace App\Enums;

enum Role: string {
    case MASTER_ADMIN = 'master_admin';
    case ADMIN        = 'admin';
    case FULFILLER    = 'fulfiller';
    case CLIENT       = 'user';
    case CUSTOMER     = 'player';
}
