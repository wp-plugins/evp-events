<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Evp\Bundle\TicketBundle\EvpTicketBundle(),
            new Evp\Bundle\TicketAdminBundle\EvpTicketAdminBundle(),
            new Evp\Bundle\PaymentBundle\EvpPaymentBundle(),
            new Evp\Bundle\WebToPayBundle\EvpWebToPayBundle(),
            new Evp\Bundle\TicketMaintenanceBundle\EvpTicketMaintenanceBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Exercise\HTMLPurifierBundle\ExerciseHTMLPurifierBundle(),
            new Ivory\CKEditorBundle\IvoryCKEditorBundle(),
            new Evp\Bundle\DeviceApiBundle\EvpDeviceApiBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new Evp\Bundle\ReportingBundle\EvpReportingBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
