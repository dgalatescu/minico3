<?php
/**
 * Created by PhpStorm.
 * User: dan.galatescu
 * Date: 12/23/2014
 * Time: 5:12 PM
 */

namespace  MinicoSilverBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContainsAlphanumericValidator extends ConstraintValidator {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validate($value, Constraint $constraint)
    {
        $this->entityManager->getRepository('MinicoSilverBundle:Storage');
        $limit = 0;


        $qty = 0;
        if ($qty<0) {
            // If you're using the new 2.5 validation API (you probably are!)
            $this->context->addViolation(
                'The max value should be {{ limit }}.',
                array(
                    '{{ limit }}' => $limit
                )
            );

//                ->buildViolation($constraint->message)
//                ->setParameter('%string%', $value)
//                ->addViolation();


            // If you're using the old 2.4 validation API
            /*
            $this->context->addViolation(
                $constraint->message,
                array('%string%' => $value)
            );
            */
        }
    }
}
