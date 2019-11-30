<?php

namespace ContactBundle\Form;

use ContactBundle\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_name', null, ['required' => true])
            ->add('last_name', null, ['required' => true])
            ->add('street', null, ['required' => true])
            ->add('zip', null, ['required' => true])
            ->add('city', null, ['required' => true])
            ->add('country', null, ['required' => true])
            ->add('phone_number', TelType::class, ['required' => true])
            ->add('dateOfBirth', BirthdayType::class, ['widget' => 'single_text', 'format' => 'dd.MM.yyyy'])
            ->add('email_address', EmailType::class)
            ->add('picture', FileType::class, ['required' => false, 'data_class' => null]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
