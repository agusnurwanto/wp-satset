<?php
    $gambar_menu_default = 'https://via.placeholder.com/25x25';
    $gambar_menu0 = get_option('_crb_satset_menu_logo_0');
    if(empty($gambar_menu0)){
        $gambar_menu0 = $gambar_menu_default;
    }
    $gambar_menu1 = get_option('_crb_satset_menu_logo_1');
    if(empty($gambar_menu1)){
        $gambar_menu1 = $gambar_menu_default;
    }
    $gambar_menu2 = get_option('_crb_satset_menu_logo_2');
    if(empty($gambar_menu2)){
        $gambar_menu2 = $gambar_menu_default;
    }
    $gambar_menu3 = get_option('_crb_satset_menu_logo_3');
    if(empty($gambar_menu3)){
        $gambar_menu3 = $gambar_menu_default;
    }
    $gambar_menu4 = get_option('_crb_satset_menu_logo_4');
    if(empty($gambar_menu4)){
        $gambar_menu4 = $gambar_menu_default;
    }
    $gambar_menu5 = get_option('_crb_satset_menu_logo_5');
    if(empty($gambar_menu5)){
        $gambar_menu5 = $gambar_menu_default;
    }
    $gambar_menu6 = get_option('_crb_satset_menu_logo_6');
    if(empty($gambar_menu6)){
        $gambar_menu6 = $gambar_menu_default;
    }
    $gambar_menu7 = get_option('_crb_satset_menu_logo_7');
    if(empty($gambar_menu7)){
        $gambar_menu7 = $gambar_menu_default;
    }
    $gambar_menu8 = get_option('_crb_satset_menu_logo_8');
    if(empty($gambar_menu8)){
        $gambar_menu8 = $gambar_menu_default;
    }
    $gambar_menu9 = get_option('_crb_satset_menu_logo_9');
    if(empty($gambar_menu9)){
        $gambar_menu9 = $gambar_menu_default;
    }
    $gambar_menu10 = get_option('_crb_satset_menu_logo_10');
    if(empty($gambar_menu10)){
        $gambar_menu10 = $gambar_menu_default;
    }
    $gambar_menu11 = get_option('_crb_satset_menu_logo_11');
    if(empty($gambar_menu11)){
        $gambar_menu11 = $gambar_menu_default;
    }
    $gambar_menu12 = get_option('_crb_satset_menu_logo_12');
    if(empty($gambar_menu12)){
        $gambar_menu12 = $gambar_menu_default;
    }
    $gambar_menu13 = get_option('_crb_satset_menu_logo_13');
    if(empty($gambar_menu13)){
        $gambar_menu13 = $gambar_menu_default;
    }
    $gambar_menu14 = get_option('_crb_satset_menu_logo_14');
    if(empty($gambar_menu14)){
        $gambar_menu14 = $gambar_menu_default;
    }
    $gambar_menu15 = get_option('_crb_satset_menu_logo_15');
    if(empty($gambar_menu15)){
        $gambar_menu15 = $gambar_menu_default;
    }
    $gambar_menu16 = get_option('_crb_satset_menu_logo_16');
    if(empty($gambar_menu16)){
        $gambar_menu16 = $gambar_menu_default;
    }
?>
<script type="text/javascript" src="<?php echo SATSET_PLUGIN_URL; ?>public/js/loadingoverlay.min.js"></script>
<script type="text/javascript">
<?php
    $src = 'src="'.get_option('_crb_satset_menu_video_loading').'"';
?>
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
</style>
<section id="sewa_aset">
    <div class="container intro-text">
        <div class="row text-center">
            <div class="col-md-12" style="margin-top: 35px;">
                <a class="main animated" data-animation="fadeInUp" data-animation-delay="1000" href="<?php echo site_url(); ?>">
                    <img class="site-logo" src="<?php echo get_option('_crb_satset_menu_logo_dashboard'); ?>" alt="SIMATA" />
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
        <div class="row counting-box title-row text-center">
            <div class="col-md-2 col-xs-6 animated" data-animation="fadeInLeft" data-animation-delay="1000">
                <div class="setbulet bg-info pull-up">
                    <a href="<?php echo get_option('_crb_satset_menu_url_1'); ?>" target="_blank">
                        <img src="<?php echo $gambar_menu1; ?>">
                    </a>
                </div>
                <a href="<?php echo get_option('_crb_satset_menu_url_1'); ?>" target="_blank">
                    <h3 class="normal text-white text-xbold text-shadow"><?php echo get_option('_crb_satset_menu_text_1'); ?></h3>
                </a>
            </div>
            <div class="col-md-2 col-xs-6 animated" data-animation="fadeInLeft" data-animation-delay="1000">
                <div class="setbulet bg-info pull-up">
                    <a href="<?php echo get_option('_crb_satset_menu_url_2'); ?>" target="_blank">
                        <img src="<?php echo $gambar_menu2; ?>">
                    </a>
                </div>
                <a href="<?php echo get_option('_crb_satset_menu_url_2'); ?>" target="_blank">
                    <h3 class="normal text-white text-xbold text-shadow"><?php echo get_option('_crb_satset_menu_text_2'); ?></h3>
                </a>
            </div>
            <div class="col-md-2 col-xs-6 animated" data-animation="fadeInUp" data-animation-delay="1000">
                <div class="setbulet bg-info pull-up">
                    <a href="<?php echo get_option('_crb_satset_menu_url_3'); ?>" target="_blank">
                        <img src="<?php echo $gambar_menu3; ?>">
                    </a>
                </div>
                <a href="<?php echo get_option('_crb_satset_menu_url_3'); ?>" target="_blank">
                    <h3 class="normal text-white text-xbold text-shadow"><?php echo get_option('_crb_satset_menu_text_3'); ?></h3>
                </a>
            </div>
            <div class="col-md-2 col-xs-6 animated" data-animation="fadeInUp" data-animation-delay="1000">
                <div class="setbulet bg-info pull-up">
                    <a href="<?php echo get_option('_crb_satset_menu_url_4'); ?>" target="_blank">
                        <img src="<?php echo $gambar_menu4; ?>">
                    </a>
                </div>
                <a href="<?php echo get_option('_crb_satset_menu_url_4'); ?>" target="_blank">
                    <h3 class="normal text-white text-xbold text-shadow"><?php echo get_option('_crb_satset_menu_text_4'); ?></h3>
                </a>
            </div>
            <div class="col-md-2 col-xs-6 animated" data-animation="fadeInRight" data-animation-delay="1000">
                <div class="setbulet bg-info pull-up">
                    <a href="<?php echo get_option('_crb_satset_menu_url_5'); ?>" target="_blank">
                        <img src="<?php echo $gambar_menu5; ?>">
                    </a>
                </div>
                <a href="<?php echo get_option('_crb_satset_menu_url_5'); ?>" target="_blank">
                    <h3 class="normal text-white text-xbold text-shadow"><?php echo get_option('_crb_satset_menu_text_5'); ?></h3>
                </a>
            </div>
            <div class="col-md-2 col-xs-6 animated" data-animation="fadeInRight" data-animation-delay="1000">
                <div class="setbulet bg-info pull-up">
                    <a href="<?php echo get_option('_crb_satset_menu_url_6'); ?>" target="_blank">
                        <img src="<?php echo $gambar_menu6; ?>">
                    </a>
                </div>
                <a href="<?php echo get_option('_crb_satset_menu_url_6'); ?>" target="_blank">
                    <h3 class="normal text-white text-xbold text-shadow"><?php echo get_option('_crb_satset_menu_text_6'); ?></h3>
                </a>
            </div>
        </div>
        <div class="row counting-box text-center title-row">
            <div class="col-md-2 col-xs-6 animated" data-animation="fadeInLeft" data-animation-delay="1000">
                <div class="setbulet bg-info pull-up">
                    <a href="<?php echo get_option('_crb_satset_menu_url_7'); ?>" target="_blank">
                        <img src="<?php echo $gambar_menu7; ?>">
                    </a>
                </div>
                <a href="<?php echo get_option('_crb_satset_menu_url_7'); ?>" target="_blank">
                    <h3 class="normal text-white text-xbold text-shadow"><?php echo get_option('_crb_satset_menu_text_7'); ?></h3>
                </a>
            </div>
            <div class="col-md-2 col-xs-6 animated" data-animation="fadeInLeft" data-animation-delay="1000">
                <div class="setbulet bg-info pull-up">
                    <a href="<?php echo get_option('_crb_satset_menu_url_8'); ?>" target="_blank">
                        <img src="<?php echo $gambar_menu8; ?>">
                    </a>
                </div>
                <a href="<?php echo get_option('_crb_satset_menu_url_8'); ?>" target="_blank">
                    <h3 class="normal text-white text-xbold text-shadow"><?php echo get_option('_crb_satset_menu_text_8'); ?></h3>
                </a>
            </div>
            <div class="col-md-2 col-xs-6 animated" data-animation="fadeInUp" data-animation-delay="1000">
                <div class="setbulet bg-info pull-up">
                    <a href="<?php echo get_option('_crb_satset_menu_url_9'); ?>" target="_blank">
                        <img src="<?php echo $gambar_menu9; ?>">
                    </a>
                </div>
                <a href="<?php echo get_option('_crb_satset_menu_url_9'); ?>" target="_blank">
                    <h3 class="normal text-white text-xbold text-shadow"><?php echo get_option('_crb_satset_menu_text_9'); ?></h3>
                </a>
            </div>
            <div class="col-md-2 col-xs-6 animated" data-animation="fadeInUp" data-animation-delay="1000">
                <div class="setbulet bg-info pull-up">
                    <a href="<?php echo get_option('_crb_satset_menu_url_10'); ?>" target="_blank">
                        <img src="<?php echo $gambar_menu10; ?>">
                    </a>
                </div>
                <a href="<?php echo get_option('_crb_satset_menu_url_10'); ?>" target="_blank">
                    <h3 class="normal text-white text-xbold text-shadow"><?php echo get_option('_crb_satset_menu_text_10'); ?></h3>
                </a>
            </div>
            <div class="col-md-2 col-xs-6 animated" data-animation="fadeInRight" data-animation-delay="1000">
                <div class="setbulet bg-info pull-up">
                    <a href="<?php echo get_option('_crb_satset_menu_url_11'); ?>" target="_blank">
                        <img src="<?php echo $gambar_menu11; ?>">
                    </a>
                </div>
                <a href="<?php echo get_option('_crb_satset_menu_url_11'); ?>" target="_blank">
                    <h3 class="normal text-white text-xbold text-shadow"><?php echo get_option('_crb_satset_menu_text_11'); ?></h3>
                </a>
            </div>
            <div class="col-md-2 col-xs-6 animated" data-animation="fadeInRight" data-animation-delay="1000">
                <div class="setbulet bg-info pull-up">
                    <a href="#hide-menu" onclick="show_more(); return false;">
                        <img src="<?php echo $gambar_menu0; ?>">
                    </a>
                </div>
                <a href="#hide-menu" onclick="show_more(); return false;">
                    <h3 class="normal text-white text-xbold text-shadow" id="text-lainya"><?php echo get_option('_crb_satset_menu_text_0'); ?></h3>
                </a>
            </div>
        </div>
        <div id="hide-menu" class="row counting-box text-center title-row">
            <div class="col-md-2 col-xs-6 animated" data-animation="fadeInUp" data-animation-delay="200">
                <div class="setbulet bg-info pull-up">
                    <a href="<?php echo get_option('_crb_satset_menu_url_12'); ?>" target="_blank">
                        <img src="<?php echo $gambar_menu12; ?>">
                    </a>
                </div>
                <a href="<?php echo get_option('_crb_satset_menu_url_12'); ?>" target="_blank">
                    <h3 class="normal text-white text-xbold text-shadow"><?php echo get_option('_crb_satset_menu_text_12'); ?></h3>
                </a>
            </div>
            <div class="col-md-2 col-xs-6 animated" data-animation="fadeInUp" data-animation-delay="200">
                <div class="setbulet bg-info pull-up">
                    <a href="<?php echo get_option('_crb_satset_menu_url_13'); ?>" target="_blank">
                        <img src="<?php echo $gambar_menu13; ?>">
                    </a>
                </div>
                <a href="<?php echo get_option('_crb_satset_menu_url_13'); ?>" target="_blank">
                    <h3 class="normal text-white text-xbold text-shadow"><?php echo get_option('_crb_satset_menu_text_13'); ?></h3>
                </a>
            </div>
            <div class="col-md-2 col-xs-6 animated" data-animation="fadeInUp" data-animation-delay="200">
                <div class="setbulet bg-info pull-up">
                    <a href="<?php echo get_option('_crb_satset_menu_url_14'); ?>" target="_blank">
                        <img src="<?php echo $gambar_menu14; ?>">
                    </a>
                </div>
                <a href="<?php echo get_option('_crb_satset_menu_url_14'); ?>" target="_blank">
                    <h3 class="normal text-white text-xbold text-shadow"><?php echo get_option('_crb_satset_menu_text_14'); ?></h3>
                </a>
            </div>
            <div class="col-md-2 col-xs-6 animated" data-animation="fadeInUp" data-animation-delay="200">
                <div class="setbulet bg-info pull-up">
                    <a href="<?php echo get_option('_crb_satset_menu_url_15'); ?>" target="_blank">
                        <img src="<?php echo $gambar_menu15; ?>">
                    </a>
                </div>
                <a href="<?php echo get_option('_crb_satset_menu_url_15'); ?>" target="_blank">
                    <h3 class="normal text-white text-xbold text-shadow"><?php echo get_option('_crb_satset_menu_text_15'); ?></h3>
                </a>
            </div>
            <div class="col-md-2 col-xs-6 animated" data-animation="fadeInUp" data-animation-delay="200">
                <div class="setbulet bg-info pull-up">
                    <a href="<?php echo get_option('_crb_satset_menu_url_16'); ?>" target="_blank">
                        <img src="<?php echo $gambar_menu16; ?>">
                    </a>
                </div>
                <a href="<?php echo get_option('_crb_satset_menu_url_16'); ?>" target="_blank">
                    <h3 class="normal text-white text-xbold text-shadow"><?php echo get_option('_crb_satset_menu_text_16'); ?></h3>
                </a
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

    function show_more(){
        if(jQuery('#hide-menu').hasClass('show')){
            jQuery('#hide-menu').removeClass('show');
            jQuery('#text-lainya').text(jQuery('#hide-menu').attr('text-asli'));
        }else{
            jQuery('#hide-menu').addClass('show');
            jQuery('#hide-menu').attr('text-asli', jQuery('#text-lainya').text());
            jQuery('#text-lainya').text('Sembunyikan');
            jQuery("html, body").animate({ 
                scrollTop: jQuery(document).height()
            }, 1000);
        }
    }
</script>
