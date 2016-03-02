<?php

namespace CTO\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class AdminUser
 *
 * @ORM\Entity(repositoryClass="CTO\AppBundle\Entity\Repository\AdminUserRepository")
 */
class AdminUser extends BaseUser
{
    public function __construct()
    {
        parent::__construct();
        $this->setRoles([self::ROLE_ADMIN_USER, 'ROLE_ALLOWED_TO_SWITCH']);
    }
}
