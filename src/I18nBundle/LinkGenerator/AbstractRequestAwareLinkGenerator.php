<?php

namespace I18nBundle\LinkGenerator;

use I18nBundle\Builder\RouteParameterBuilder;
use Pimcore\Http\Request\Resolver\SiteResolver;
use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractRequestAwareLinkGenerator
{
    protected UrlGeneratorInterface $urlGenerator;

    protected RequestStack $requestStack;

    protected int $urlReferenceType = UrlGeneratorInterface::ABSOLUTE_PATH;

    #[Required]
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator): void
    {
        $this->urlGenerator = $urlGenerator;
    }

    #[Required]
    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    public function generate(Concrete $object, array $params = []): string
    {
        // Use this class, if you want to use object links in wysiwyg or link elements
        // Be aware, that this class relies on the main request!

        $routeParams = [];
        $routeContext = [];

        if (!$this->requestStack->getMainRequest() instanceof Request) {
            return '#';
        }

        $routeParams['_locale'] = $this->requestStack->getMainRequest()->getLocale();

        if ($this->requestStack->getMainRequest()->attributes->has(SiteResolver::ATTRIBUTE_SITE)) {
            $routeContext['site'] = $this->requestStack->getMainRequest()->attributes->get(SiteResolver::ATTRIBUTE_SITE);
        }

        $routeItemParameters = RouteParameterBuilder::buildForEntity($object, $routeParams, $routeContext);

        return $this->urlGenerator->generate('', $routeItemParameters, $this->urlReferenceType);
    }
}
