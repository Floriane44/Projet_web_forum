<?php

namespace Projet\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType {

 	public function buildForm(FormBuilderInterface $builder, array $options){
        $builder->add('image', ImageType::class, array('required' => false));
    }

    public function getParent(){
        return 'FOS\UserBundle\Form\Type\ProfileFormType';
    }

    public function getBlockPrefix(){
        return 'app_user_profile';
    }

    public function getName(){
        return $this->getBlockPrefix();
    }
}