<?php
/**
 * Created by PhpStorm.
 * User: upv
 * Date: 16/12/15
 * Time: 22:56
 */

namespace SistemasWeb\AlbumBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email',EmailType::class );
    }
}