<?php

namespace CPAlex\DashboardBundle\Listener;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 *  @DI\Service()
 */
class CPAlexListener
{
    private $container;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container"),
     *     "templating" = @DI\Inject("templating")
     * })
     */
    public function __construct(ContainerInterface $container,TwigEngine $templating)
    {
        $this->container = $container;
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("open_tool_workspace_CPAlex")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspace(DisplayToolEvent $event)
    {
//        $content = $this->templating->render(
//            'CPAlexDashboardBundle::vue.html.twig',
//            array(
//            )
//        );
//		$content = 'Alex';
//        $event->setContent($content);
//        $event->stopPropagation();
        $subRequest = $this->container->get('request')->duplicate(array(), null, array("_controller" => 'CPAlexDashboardBundle:Dashboard:Filter'));
        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setContent($response->getContent());
    }

    /**
     * @DI\Observe("open_tool_desktop_CPAlex")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktop(DisplayToolEvent $event)
    {
//        $content = $this->templating->render(
//            'CPAlexDashboardBundle::vue.html.twig',
//            array(
//            )
      //  );
//        $route = $this->container->get('router')->generate('testAction');
//		$content = 'Alex';
//        $event->setResponse(new RedirectResponse($route));
//        $event->stopPropagation();
        $subRequest = $this->container->get('request')->duplicate(array(), null, array("_controller" => 'CPAlexDashboardBundle:Dashboard:Filter'));
        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setContent($response->getContent());
    }

}
