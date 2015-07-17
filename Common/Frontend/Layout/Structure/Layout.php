<?php
namespace SPHERE\Common\Frontend\Layout\Structure;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\Layout\ILayoutInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Layout
 *
 * @package SPHERE\Common\Frontend\Layout\Structure
 */
class Layout extends Extension implements ILayoutInterface
{

    /** @var LayoutGroup[] $LayoutGroup */
    protected $LayoutGroup = array();
    /** @var IBridgeInterface $Template */
    private $Template = null;

    /**
     * @param LayoutGroup|LayoutGroup[] $LayoutGroup
     */
    public function __construct( $LayoutGroup )
    {

        if (!is_array( $LayoutGroup )) {
            $LayoutGroup = array( $LayoutGroup );
        }
        $this->LayoutGroup = $LayoutGroup;
        $this->Template = $this->getTemplate( __DIR__.'/Layout.twig' );
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $this->Template->setVariable( 'Layout', $this->LayoutGroup );
        return $this->Template->getContent();
    }
}
