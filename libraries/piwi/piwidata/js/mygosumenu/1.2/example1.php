<?php
/*
 * PROJECT:   mygosuMenu
 * VERSION:   1.2.0
 * COPYRIGHT: (c) 2003,2004 Cezary Tomczak
 * LINK:      http://gosu.pl/software/mygosumenu.html
 * LICENSE:   BSD (revised)
 */

/**
* Note: this function is called recursively
* @param array &$a
* @param string $id (optional)
* @return string
*/
function generateTreeMenu(&$a, $id = null) {
    $s = '<ul';
    if ($id)
        $s .= ' id="'.$id.'" class="tree-menu"';
    $s .= '>';
    foreach ($a as $k => $v) {
        if (is_array($v)) {
            $s .= '<li><a href="javascript:void(0)">'.$k.'</a>';
            $s .= generateTreeMenu($a[$k]);
            $s .= '</li>';
        } else {
            $s .= '<li><a href="'.$v.'">'.$k.'</a></li>';
        }
    }
    $s .= '</ul>';
    return $s;
}

$menu = array(
    'Products' => array(
        'Product One' => '#',
        'Product Two' => array(
            'Overview' => '#',
            'Features' => '#',
            'Requirements' => '#',
            'Flash Demos' => '#'
        ),
        'Product Three' => array(
            'Overview' => '#',
            'Features' => '#',
            'Requirements' => '#',
            'Screenshots' => '#',
            'Flash Demos' => '#',
            'Live Demo' => array(
                'Create Account' => '#',
                'Test Drive' => array(
                    'Test One' => '#',
                    'Test Two' => '#',
                    'Test Three' => '#'
                )
            )
        ),
        'Product Four' => array(
            'Overview' => '#',
            'Features' => '#',
            'Requirements' => '#'
        ),
        'Product Five' => '#'
    ),
    'Downloads' => array(
        '30-day Demo Key' => '#',
        'Product One Download' => array(
            'Windows Download' => '#',
            'Solaris Download' => '#',
            'Linux Download' => '#'
        ),
        'Product Two Download' => array(
            'Linux Download' => '#'
        )
    ),
    'Support' => array(
        'E-mail Support' => '#'
    ),
    'Partners' => array(
        'Partner Benefits' => '#',
        'Partner Application' => array(
            'Application One' => '#',
            'Application Two' => '#',
            'Application Three' => '#',
            'Application Four' => '#',
            'Application Five' => '#',
            'Application Six' => '#',
            'Application Seven' => '#',
            'Application Eight' => '#'
        ),
        'Partner Listing' => '#'
    ),
    'Customers' => array(
        'Customer One' => '#',
        'Customer Two' => '#',
        'Customer Three' => '#'
    ),
    'About Us' => array(
        'Executive Team' => '#',
        'Investors' => '#',
        'Career Opportunities' => '#',
        'Press Center' => array(
            'Product Information' => '#'
        ),
        'Success Stories' => '#',
        'Contact Us' => '#'
    )
);

?>

<html>
<head>
    <link rel="stylesheet" type="text/css" href="TreeMenu.css">
    <script type="text/javascript" src="TreeMenu.js"></script>
</head>
<body>

<script type="text/javascript">
window.onload = function() {
    new TreeMenu("menu1");
}
</script>

<?php echo generateTreeMenu($menu, 'menu1'); ?>

</body>
</html>