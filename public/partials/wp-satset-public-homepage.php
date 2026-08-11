<?php
    $gambar_menu_default = 'https://via.placeholder.com/25x25';
    $active_menus = array();
    for($i = 1; $i <= 16; $i++){
        $text = get_option('_crb_satset_menu_text_'.$i);
        $url = get_option('_crb_satset_menu_url_'.$i);
        $logo = get_option('_crb_satset_menu_logo_'.$i);
        
        if(!empty($logo)){
            $active_menus[] = array(
                'text' => $text,
                'url' => $url,
                'logo' => $logo,
            );
        }
    }
    
    $total_menus = count($active_menus);
    $col_md = 4; // default 3 items per row
    if($total_menus == 1) $col_md = 12;
    elseif($total_menus == 2) $col_md = 6;
    elseif($total_menus == 3) $col_md = 4;
    elseif($total_menus >= 4 && $total_menus <= 8) $col_md = 3;
    elseif($total_menus >= 9) $col_md = 2;
    
    $col_class = 'col-md-' . $col_md;
?>
<script type="text/javascript" src="<?php echo SATSET_PLUGIN_URL; ?>public/js/loadingoverlay.min.js"></script>
<?php
    $video = get_option('_crb_satset_menu_video_loading');
    if(!empty($video)):
        $src = 'src="'.$video.'"';
?>
<script type="text/javascript">
    var $ = jQuery;
    function progressLoading() {
        $.LoadingOverlay('show', { 
            image : '', 
            custom : '<video style="position: absolute; width: 90%; top: 0; margin: auto;" autoplay muted><source <?php echo $src; ?> type="video/mp4">Your browser does not support the video tag.</video>', 
            imageAnimation : false,
            background : "rgba(0, 0, 0, 1)" 
        });
    }
    progressLoading();
    setTimeout(function(){
        $(document).ready(function() { $.LoadingOverlay('hide'); });
        jQuery('body').addClass('bg-infinity');
    }, <?php echo get_option('_crb_satset_lama_loading'); ?>);
</script>
<?php
    endif;
?>
<!-- CSS Begins-->
<link href="<?php echo SATSET_PLUGIN_URL; ?>public/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo SATSET_PLUGIN_URL; ?>public/css/flaticon.css" rel="stylesheet" type="text/css" />
<link href="<?php echo SATSET_PLUGIN_URL; ?>public/css/bootstrap.part1.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo SATSET_PLUGIN_URL; ?>public/css/bootstrap.part2.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo SATSET_PLUGIN_URL; ?>public/css/portfolio.css" rel="stylesheet" type="text/css" />
<link href="<?php echo SATSET_PLUGIN_URL; ?>public/css/animate.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo SATSET_PLUGIN_URL; ?>public/css/prettyPhoto.css" rel="stylesheet" type="text/css" />
<link href="<?php echo SATSET_PLUGIN_URL; ?>public/css/flexslider.css" rel="stylesheet" type="text/css" />
<link href="<?php echo SATSET_PLUGIN_URL; ?>public/css/tweet-carousel.css" rel="stylesheet" type="text/css" />
<link href="<?php echo SATSET_PLUGIN_URL; ?>public/css/vegas.min.css" rel="stylesheet" type="text/css" />
<!-- Main Style -->
<link href="<?php echo SATSET_PLUGIN_URL; ?>public/css/style.css" rel="stylesheet" type="text/css" />
<link href="<?php echo SATSET_PLUGIN_URL; ?>public/css/responsive.css" rel="stylesheet" type="text/css /">
<!-- Color Panel -->
<link href="<?php echo SATSET_PLUGIN_URL; ?>public/css/color_panel.css" rel="stylesheet" type="text/css /">
<!-- Skin Colors -->
<link href="<?php echo SATSET_PLUGIN_URL; ?>public/css/landing.css" id="changeable-colors" rel="stylesheet" type="text/css" />
<!-- Custom Styles -->
<link href="<?php echo SATSET_PLUGIN_URL; ?>public/css/parallax-star.css" rel="stylesheet" type="text/css" />
<link href="<?php echo SATSET_PLUGIN_URL; ?>public/css/floating-cloud.css" rel="stylesheet" type="text/css" />
<link href="<?php echo SATSET_PLUGIN_URL; ?>public/css/infinity.css" rel="stylesheet" type="text/css" />
<link href="<?php echo SATSET_PLUGIN_URL; ?>public/css/bgsliding.css" rel="stylesheet" type="text/css" />
<style type="text/css">
    .page-title {
        background-image:
            linear-gradient(rgb(14 66 95 / 90%), rgb(32 79 105 / 60%)),
            url(https://images.unsplash.com/photo-1492546643178-96d64f3fd824?auto=format&fit=crop&w=1051&q=25)
            !important;
    }
    .bg-overlay.pattern {
        background:url(<?php echo SATSET_PLUGIN_URL; ?>imagespublic/pattern.png);
        filter:progid: DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr='#7c000000',endColorstr='#7c000000');
        /* IE */
    }
    .navbar ul.nav a {
        text-decoration: none;
    }
    h3.normal {
        font-weight: bold;
        font-size: 15px;
    }
    .factor {
        word-break: break-all;
    }
    .navbar-fixed-bottom .navbar-collapse, .navbar-fixed-top .navbar-collapse {
        max-height: inherit;
    }
    .container > div.row > div.text-center {
        margin-top: 0;
    }
    .counting-box > div {
        margin-bottom: 45px;
    }
    .setbulet {
        padding: 15px;
        width: 88px;
        margin: auto;
        border-radius: 50%;
        cursor: pointer;
        box-shadow: inset 0 0 4px #2e3642;
    }
    .setbulet img{
        height: 60px;
        width: 60px;
    }
    .text-shadow {
        text-shadow: 2px 2px 4px #000000;
    }
    .text-xbold {
        font-weight: bold;
    }
    .text-white {
        color: #fff;
    }
    .intro-text h1 {
        font-size: 40px;
        color: #fff;
    }
    .pull-up {
        transition: all 0.25s ease; 
    }
    .pull-up:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0px 14px 24px rgb(62 57 107 / 20%);
        z-index: 999;
        box-shadow: inset 0 0 4px #2e3642;
    }
    #hide-menu {
        display: none;
    }
    #hide-menu.show {
        display: inherit;
    }
    .site-logo {
        max-width: 500px;
	    width: 100%;
    }
    #page {
        background-color: rgba(0, 0, 0, 0.5);
    }
</style>
<section>
    <div class="container intro-text">
        <div class="row text-center">
            <div class="col-md-12" style="margin-top: 35px;">
                <a class="main animated" data-animation="fadeInUp" data-animation-delay="1000" href="<?php echo site_url(); ?>">
                    <img class="site-logo" src="<?php echo get_option('_crb_satset_menu_logo_dashboard'); ?>" alt="SATSET" />
                </a>
            </div>
        </div>
        <div class="row text-center">
            <div class="col-md-12">
                <div class="main animated" data-animation="fadeInUp" data-animation-delay="1000">
                    <h1 class="text-shadow" style="padding-top: 0 !important;padding-bottom: 50px; margin-top: 20px !important;"><?php echo get_option('_crb_satset_judul_header'); ?></h1>
                </div>
            </div>
        </div>
        <div class="row counting-box title-row text-center" style="display: flex; flex-wrap: wrap; justify-content: center;">
            <?php foreach($active_menus as $index => $menu): 
                $animations = array('fadeInLeft', 'fadeInUp', 'fadeInRight');
                $anim = $animations[$index % 3];
            ?>
            <div class="<?php echo $col_class; ?> col-xs-6 animated" data-animation="<?php echo $anim; ?>" data-animation-delay="1000">
                <div class="setbulet bg-info pull-up">
                    <a href="<?php echo $menu['url']; ?>" target="_blank">
                        <img src="<?php echo $menu['logo']; ?>">
                    </a>
                </div>
                <a href="<?php echo $menu['url']; ?>" target="_blank">
                    <h3 class="normal text-white text-xbold text-shadow"><?php echo $menu['text']; ?></h3>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script type="text/javascript" src="<?php echo SATSET_PLUGIN_URL; ?>public/js/jquery.sticky.js"></script>
<!-- Slider and Features Canvas -->
<script type="text/javascript" src="<?php echo SATSET_PLUGIN_URL; ?>public/js/jquery.flexslider-min.js"></script>
<script type="text/javascript" src="<?php echo SATSET_PLUGIN_URL; ?>public/js/vegas.min.js"></script>
<!-- Overlay -->
<script type="text/javascript" src="<?php echo SATSET_PLUGIN_URL; ?>public/js/modernizr.js"></script>
<!-- Screenshot -->
<script type="text/javascript" src="<?php echo SATSET_PLUGIN_URL; ?>public/js/jquery.flexisel.js"></script>
<!-- Portfolio -->
<script type="text/javascript" src="<?php echo SATSET_PLUGIN_URL; ?>public/js/jquery.prettyPhoto.js"></script>
<script type="text/javascript" src="<?php echo SATSET_PLUGIN_URL; ?>public/js/jquery.mixitup.min.js"></script>
<script type="text/javascript" src="<?php echo SATSET_PLUGIN_URL; ?>public/js/jquery.fitvids.js"></script>
<script type="text/javascript" src="<?php echo SATSET_PLUGIN_URL; ?>public/js/jquery.easing.1.3.js"></script>
<!-- Counting Section -->
<script type="text/javascript" src="<?php echo SATSET_PLUGIN_URL; ?>public/js/jquery.appear.js"></script>
<!-- Expertise Circular Progress Bar -->
<script type="text/javascript" src="<?php echo SATSET_PLUGIN_URL; ?>public/js/effect.js"></script>
<!-- Twitter -->
<script type="text/javascript" src="<?php echo SATSET_PLUGIN_URL; ?>public/js/carousel.js"></script>
<script type="text/javascript" src="<?php echo SATSET_PLUGIN_URL; ?>public/js/custom.js"></script>
<script type="text/javascript" src="<?php echo SATSET_PLUGIN_URL; ?>public/js/delaunator.min.js"></script>
<script type="text/javascript" src="<?php echo SATSET_PLUGIN_URL; ?>public/js/rainbow-lines.js"></script>
<!-- Color -->
<script type="text/javascript" src="<?php echo SATSET_PLUGIN_URL; ?>public/js/color-panel.js"></script>
<script type="text/javascript">
<?php
    $background_header_db = $this->functions->get_option_complex('_crb_satset_background_beranda', 'beranda');
    $background_header = array();
    foreach($background_header_db as $background){
        $background_header[] = array('src' => $background['gambar']);
    }
    echo 'var background_header = '.json_encode($background_header).';';
?>
    jQuery('body').vegas({
        slides: background_header
    });
</script>
