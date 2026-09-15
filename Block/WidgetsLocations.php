<?php
namespace Yotpo\Reviews\Block;

/**
 * Enum WidgetsLocations - Magento page handles a Yotpo widget can be rendered on
 */
enum WidgetsLocations: string
{
    case HOME = 'cms_index_index';
    case CATEGORY = 'catalog_category_view';
    case OTHER = '';
}
