<?php

namespace Cunningsoft\AchievementBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Parser;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CunningsoftAchievementExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $yaml = new Parser();
        $locator = new FileLocator($container->getParameter('kernel.root_dir').'/config');
        $achievementCategories = $yaml->parse(file_get_contents($locator->locate('achievements.yml')));

        foreach ($achievementCategories as $category => $achievements) {
            foreach ($achievements as $achievementId => $achievement) {
                $definition = new Definition();
                $definition->setClass($achievement['class']);
                $definition->setArguments(array(new Reference('cunningsoft.achievement.service'), new Reference('doctrine.orm.entity_manager')));
                $definition->addTag('kernel.event_listener', array('event' => $achievement['event'], 'method' => $achievement['method']));
                $container->setDefinition('cunningsoft.achievement.listener.' . $category . '.' . $achievementId, $definition);
            }
        }
    }
}
