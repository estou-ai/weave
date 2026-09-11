<?php

namespace Estouai\Weave\Support;

// Curated Statamic CP icon names the addon's own blocks use. Not exhaustive —
// `icon()` accepts any CP icon name as a plain string too (the CP ships
// hundreds), and a raw `<svg>` string for a fully custom icon.
enum BlockIcon: string
{
    case PuzzlePiece = 'puzzle-piece';
    case CursorClick = 'cursor-click';
    case ContainerAdd = 'container-add';
    case Columns = 'columns';
    case Hr = 'hr';
    case LayoutSplitRight = 'layout-split-right';
    case LayoutGrid = 'layout-grid';
    case LayoutFourColumns = 'layout-four-columns';
    case Forms = 'forms';
    case H2 = 'h2';
    case AlignLeft = 'align-left';
    case HeroImageAboveText = 'hero-image-above-text';
    case MediaImagePhotoFocusFrame = 'media-image-photo-focus-frame';
    case MediaNewsPaper = 'media-news-paper';
    case MapSearch = 'map-search';
    case ArrowsFitToHeight = 'arrows-fit-to-height';
    case ChartsDonutGraph = 'charts-donut-graph';
    case Quote = 'quote';
    case TextFormattingParagraph = 'text-formatting-paragraph';
}
