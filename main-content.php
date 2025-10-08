<?php // main-content.php ?>
<main>
<style>
.main-container{max-width:1400px;margin:1rem auto;background:#1A1A1A;border-radius:10px;}
.content-wrapper{padding:1.5rem;}
/* Hero */
.hero-section{position:relative;}
.hero-swiper{width:100%;border-radius:8px;overflow:hidden;aspect-ratio:7/2;}
.hero-swiper img{width:100%;height:100%;display:block;object-fit:cover;}
/* Winners */
.live-winners-container{display:flex;align-items:center;margin-top:1.5rem;}
.live-winners-container svg.live-icon{flex-shrink:0;height:50px;width:auto;}
.winners-swiper{overflow:hidden;margin-left:.5rem;}
.winners-swiper .swiper-slide{
    display:flex !important;align-items:center;gap:.75rem;padding:.75rem 1.75rem;border:1px solid #3a3a3c;border-radius:.5rem;width:auto !important;cursor:pointer;user-select:none;transition:background-color .2s;
}
.winners-swiper .swiper-slide:hover{background:#3A3A3C;}
.winners-swiper .winner-card-img{width:2rem;height:2rem;object-fit:contain;}
.winner-info{display:flex;flex-direction:column;font-size:.75rem;max-width:6.5rem;overflow:hidden;}
.winner-name,.prize-name{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.winner-name{color:rgba(251,191,36,.75);font-weight:500;}
.prize-name{color:#8E8E93;font-weight:500;}
.prize-value{font-weight:600;}
.prize-value-currency{color:var(--primary);}

/* RESPONSIVO */
@media (max-width:992px){
    .content-wrapper{padding:1.25rem;}
    .hero-swiper{aspect-ratio:16/7;}
}
@media (max-width:480px){
    .main-container{margin:.75rem .5rem;border-radius:8px;}
    .content-wrapper{padding:1rem .9rem;}
    .hero-swiper{aspect-ratio:32/9;border-radius:6px;}
    .live-winners-container{margin-top:1rem;align-items:flex-start;gap:.5rem;}
    .live-winners-container svg.live-icon{height:50px; flex-shrink: 0; width: auto;}
    .winners-swiper{margin-left:.4rem;}
    .winners-swiper .swiper-slide{padding:.6rem 1.1rem;gap:.6rem;}
    .winner-info{font-size:.7rem;max-width:5.5rem;}
}
@media (max-height:600px){
    .hero-swiper{aspect-ratio:16/6;}
}
</style>

<div class="main-container">
    <div class="content-wrapper">

        <!-- HERO -->
        <section class="hero-section">
            <div class="swiper hero-swiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide"><a href="#"><img src="https://ik.imagekit.io/kyjz2djk3p/deposito3x.png?updatedAt=1757428651160" alt="Banner 3"></a></div>
                    <div class="swiper-slide"><a href="#"><img src="https://ik.imagekit.io/3kbnnws8u/BANNER-MULTIPLICADOR.png?updatedAt=1757353289429" alt="Banner 1"></a></div>
                    <div class="swiper-slide"><a href="#"><img src="https://ik.imagekit.io/kyjz2djk3p/BANNER---RASPAGREEN2.png" alt="Banner 2"></a></div>

                </div>
                <div class="swiper-pagination"></div>
            </div>
        </section>

        <!-- WINNERS -->
        <div class="live-winners-container">
            <svg class="live-icon" viewBox="0 0 59 60" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.381 31.8854L0.250732 32.1093L5.76436 16.3468L8.04082 16.1075L13.5753 30.7088L11.4242 30.9349L10.0667 27.2976L3.71764 27.9649L2.381 31.8854ZM6.64153 19.5306L4.34418 26.114L9.461 25.5762L7.14277 19.4779C7.101 19.3283 7.05227 19.1794 6.99657 19.0313C6.94088 18.8691 6.90607 18.7328 6.89215 18.6222C6.8643 18.7372 6.82949 18.8808 6.78772 19.0532C6.74595 19.2116 6.69722 19.3707 6.64153 19.5306Z" fill="#7B869D"></path><path d="M28.5469 21.5332C28.5469 23.0732 28.2336 24.4711 27.6071 25.727C26.9945 26.9674 26.1382 27.9814 25.0382 28.769C23.9522 29.5411 22.6922 30.0026 21.2581 30.1533C19.8518 30.3011 18.5987 30.1038 17.4988 29.5614C16.4128 29.0036 15.5634 28.1688 14.9508 27.0572C14.3382 25.9456 14.0319 24.6128 14.0319 23.0588C14.0319 21.5188 14.3382 20.1286 14.9508 18.8882C15.5774 17.6464 16.4336 16.6324 17.5197 15.8462C18.6057 15.0601 19.8588 14.5924 21.2789 14.4431C22.7131 14.2924 23.9731 14.4959 25.0591 15.0538C26.1451 15.6117 26.9945 16.4464 27.6071 17.558C28.2336 18.6681 28.5469 19.9932 28.5469 21.5332ZM26.3958 21.7593C26.3958 20.5833 26.18 19.577 25.7483 18.7404C25.3306 17.9023 24.7389 17.2855 23.9731 16.8899C23.2073 16.4804 22.3093 16.3298 21.2789 16.4381C20.2625 16.5449 19.3715 16.8836 18.6057 17.4541C17.8399 18.0106 17.2412 18.7525 16.8096 19.6799C16.3919 20.6058 16.183 21.6567 16.183 22.8327C16.183 24.0087 16.3919 25.0158 16.8096 25.8539C17.2412 26.6905 17.8399 27.3136 18.6057 27.7231C19.3715 28.1326 20.2625 28.2839 21.2789 28.1771C22.3093 28.0688 23.2073 27.7294 23.9731 27.1589C24.7389 26.5745 25.3306 25.8193 25.7483 24.8934C26.18 23.966 26.3958 22.9213 26.3958 21.7593Z" fill="#7B869D"></path><path d="M5.74539 52.1851L0.200195 37.8724L3.66344 37.5084L6.46607 44.7421C6.63956 45.1801 6.79971 45.6397 6.94652 46.1208C7.09332 46.6018 7.2468 47.156 7.40695 47.7833C7.59379 47.0525 7.76061 46.4445 7.90742 45.9594C8.06757 45.4729 8.22772 44.9998 8.38787 44.5401L11.1505 36.7215L14.5336 36.3659L9.08853 51.8337L5.74539 52.1851Z" fill="#00E880"></path><path d="M19.3247 35.8623V50.7578L16.0816 51.0987V36.2032L19.3247 35.8623Z" fill="#00E880"></path><path d="M26.4195 50.0121L20.8743 35.6995L24.3375 35.3355L27.1401 42.5692C27.3136 43.0072 27.4738 43.4667 27.6206 43.9478C27.7674 44.4289 27.9209 44.9831 28.081 45.6104C28.2679 44.8795 28.4347 44.2716 28.5815 43.7864C28.7416 43.2999 28.9018 42.8268 29.0619 42.3672L31.8245 34.5486L35.2077 34.193L29.7626 49.6608L26.4195 50.0121Z" fill="#00E880"></path><path d="M49.647 40.1029C49.647 41.6193 49.3401 42.9935 48.7261 44.2255C48.1122 45.4441 47.2581 46.4397 46.1637 47.2123C45.0694 47.9714 43.8015 48.4268 42.3602 48.5782C40.9322 48.7283 39.671 48.5388 38.5766 48.0097C37.4956 47.4658 36.6482 46.6491 36.0343 45.5595C35.4337 44.4686 35.1334 43.1649 35.1334 41.6485C35.1334 40.1321 35.4404 38.7646 36.0543 37.5461C36.6682 36.314 37.5156 35.3192 38.5967 34.5614C39.691 33.7889 40.9522 33.3275 42.3802 33.1774C43.8216 33.0259 45.0827 33.2222 46.1637 33.7661C47.2581 34.2952 48.1122 35.1045 48.7261 36.1941C49.3401 37.2836 49.647 38.5866 49.647 40.1029ZM46.2238 40.4627C46.2238 39.51 46.0703 38.7142 45.7634 38.0755C45.4564 37.4234 45.016 36.9463 44.4421 36.6443C43.8816 36.3409 43.201 36.2313 42.4002 36.3155C41.5995 36.3996 40.9122 36.653 40.3383 37.0757C39.7644 37.4983 39.324 38.0679 39.017 38.7846C38.7101 39.4878 38.5566 40.3158 38.5566 41.2686C38.5566 42.2214 38.7101 43.0238 39.017 43.6759C39.324 44.3281 39.7644 44.8051 40.3383 45.1071C40.9122 45.4091 41.5995 45.5181 42.4002 45.4339C43.201 45.3497 43.8816 45.097 44.4421 44.6758C45.016 44.2398 45.4564 43.6634 45.7634 42.9467C46.0703 42.2301 46.2238 41.4021 46.2238 40.4627Z" fill="#00E880"></path><circle cx="39" cy="20" r="6" fill="#222733"></circle><g filter="url(#filter0_d_726_17235)"><circle cx="39" cy="20" r="3.75" fill="#00E880"></circle></g></svg>

            <div class="swiper winners-swiper">
                <div class="swiper-wrapper">
                    <?php
                    $winners = [
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/item_chinelo_havaianas_top_branco.png','alt'=>'Chinelo Havaianas branco','name'=>'Jonas Sa******','prize'=>'Chinelo Havaianas branco','value'=>'35,00'],
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/variant_redmi_12c_128gb_cinza.png','alt'=>'Smartphone modelo C2 NK109','name'=>'Marília Co******','prize'=>'Smartphone modelo C2 NK109','value'=>'800,00'],
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/500-REAIS.png','alt'=>'500 Reais','name'=>'Jasmin Me****','prize'=>'500 Reais','value'=>'500,00'],
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/variant_pop_110i_branco.png','alt'=>'Moto Honda Pop 110i','name'=>'Ricardo Go***','prize'=>'Moto Honda Pop 110i','value'=>'11.500,00'],
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/variant_jbl_boombox_3_black.png','alt'=>'Caixa de som JBL Boombox 3','name'=>'Cezar Qu******','prize'=>'Caixa de som JBL Boombox 3','value'=>'2.500,00'],
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/100%20REAIS.png','alt'=>'100 Reais','name'=>'Roberto Pe**','prize'=>'100 Reais','value'=>'100,00'],
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/variant_redmi_12c_128gb_cinza.png','alt'=>'Smartphone Redmi 12C','name'=>'Leo Ne***','prize'=>'Smartphone Redmi 12C','value'=>'1.400,00'],
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/item_cabo_para_recarga_usb_c.png','alt'=>'Cabo USB tipo C para recarga','name'=>'Márcio Sa****','prize'=>'Cabo USB tipo C para recarga','value'=>'360,00'],
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/item_fog_o_5_bocas.png','alt'=>'Fogão de 5 bocas','name'=>'Benedito Pa****','prize'=>'Fogão de 5 bocas','value'=>'4.800,00'],
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/variant_iphone_15_pro_256_gb_tit_nio_natural.png','alt'=>'iPhone 15 pro max','name'=>'Alessandro Az*****','prize'=>'iPhone 15 pro max','value'=>'10.800,00'],
                    ];
                    foreach($winners as $w){
                        echo '<div class="swiper-slide">';
                        echo '<img src="'.htmlspecialchars($w['img']).'" class="winner-card-img" alt="'.htmlspecialchars($w['alt']).'">';
                        echo '<div class="winner-info">';
                        echo '<span class="winner-name">'.htmlspecialchars($w['name']).'</span>';
                        echo '<p class="prize-name">'.htmlspecialchars($w['prize']).'</p>';
                        echo '<span class="prize-value"><span class="prize-value-currency">R$ </span>'.htmlspecialchars($w['value']).'</span>';
                        echo '</div></div>';
                    }
                    ?>
                </div>
            </div>
        </div> <!-- /live-winners-container -->
