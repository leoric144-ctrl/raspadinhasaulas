<?php
// Array com os dados dos prêmios
$prizes = [
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/400.000.00%20mil%20reais.png?updatedAt=1757374225104', 'name' => '50.000 Reais', 'value' => '50.000,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/item_churrasqueira_cer_mica_carv_o.png?updatedAt=1757352242852', 'name' => 'Churrasqueira', 'value' => '20.000,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/20k-removebg-preview.png?updatedAt=1757374679331', 'name' => '20.000 Reais', 'value' => '20.000,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_cg_160_start_prata_met_lico.png?updatedAt=1757352246895', 'name' => 'Moto CG', 'value' => '16.500,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_biz_110i_vermelho.png?updatedAt=1757352246694', 'name' => 'Moto Biz', 'value' => '13.000,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_pop_110i_branco.png?updatedAt=1757352247943', 'name' => 'Moto Honda', 'value' => '11.500,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_iphone_15_pro_max_256_gb_nio_preto.png?updatedAt=1757352247645', 'name' => 'iPhone 15 Pro Max', 'value' => '11.000,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/10k.png', 'name' => '10.000 Reais', 'value' => '10.000,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_iphone_15_pro_256_gb_tit_nio_natural.png?updatedAt=1757352247590', 'name' => 'iPhone 15 Pro', 'value' => '7.500,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_galaxy_z_flip5_256_gb_creme.png?updatedAt=1757352247548', 'name' => 'Galaxy Z Flip5', 'value' => '6.000,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_iphone_15_azul.png?updatedAt=1757352247562', 'name' => 'iPhone 15', 'value' => '5.000,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/item_churrasqueira_a_g_s_performance_340s.png?updatedAt=1757352242785', 'name' => 'Churrasqueira a gás', 'value' => '5.000,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/5k.png', 'name' => '5.000 Reais', 'value' => '5.000,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/item_playstation_5.png?updatedAt=1757352245144', 'name' => 'PlayStation 5', 'value' => '4.500,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_edge_40_neo_256_gb_black_beauty.png?updatedAt=1757352247482', 'name' => 'Motorola Edge 40', 'value' => '2.800,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/1K.png', 'name' => '1.000 Reais', 'value' => '1.000,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/700.png', 'name' => '700 Reais', 'value' => '700,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/500-REAIS.png', 'name' => '500 Reais', 'value' => '500,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/item_controle_dualsense_playstation_midnight_black.png?updatedAt=1757352243087', 'name' => 'Controle', 'value' => '470,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/item_fone_de_ouvido_bluetooth.png?updatedAt=1757352244406', 'name' => 'Fone de ouvido', 'value' => '170,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/100-reais.png', 'name' => '100 Reais', 'value' => '100,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/50-reais.png', 'name' => '50 Reais', 'value' => '50,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/10-reais.png', 'name' => '10 Reais', 'value' => '10,00'],
];
?>

<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

<style>
    /* --- SEÇÃO DE PRÊMIOS --- */
    .prizes-section {
        padding-top: 1rem;
    }

    .prizes-title {
        color: #f0f0f0;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    /* --- Container do Carrossel --- */
    .prizes-layout-container {
        width: 100%;
        overflow: hidden;
    }

    /* --- CARD DE PRÊMIO --- */
    .prize-card {
        background-color: #2A2A2E;
        border-radius: 8px;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: transform 0.2s ease-in-out;
        position: relative;
        overflow: hidden;
        height: 100%;
    }

    .prize-card:hover {
        transform: translateY(-5px);
    }

    .prize-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 50%;
        background: linear-gradient(to top, rgba(40, 229, 4, 0.20) 0%, transparent 80%);
        pointer-events: none;
        z-index: 1;
    }

    .prize-card > * {
        position: relative;
        z-index: 2;
    }

    .prize-card .prize-image {
        width: 100%;
        max-width: 100px;
        height: 100px;
        object-fit: contain;
    }

    .prize-card .prize-name {
        color: #E5E5E5;
        font-size: 0.9rem;
        font-weight: 500;
        min-height: 2.5em;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .prize-card .prize-value {
        background-color: #fff;
        color: #0e0b0c;
        font-weight: 510;
        font-size: 1rem;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        text-align: left;
        box-sizing: border-box;
    }

    /* --- Estilos do Carrossel para todas as telas --- */
    .prizes-layout-container {
        padding-bottom: 2rem;
    }
    .prizes-layout-container .swiper-slide {
        width: 160px; /* Largura fixa para cada card */
        flex-shrink: 0;
    }
    .prizes-layout-container .swiper-pagination-bullet {
        background: #fff;
        opacity: 0.5;
    }
    .prizes-layout-container .swiper-pagination-bullet-active {
        background: #28e504;
        opacity: 1;
    }

</style>

<section class="prizes-section">
    <h2 class="prizes-title">Prêmios da Raspadinha:</h2>

    <div class="swiper prizes-layout-container">
        <div class="swiper-wrapper">
            <?php foreach ($prizes as $prize): ?>
                <div class="swiper-slide">
                    <div class="prize-card">
                        <img src="<?php echo htmlspecialchars($prize['img']); ?>" alt="<?php echo htmlspecialchars($prize['name']); ?>" class="prize-image">
                        <p class="prize-name"><?php echo htmlspecialchars($prize['name']); ?></p>
                        <div class="prize-value">
                            R$ <?php echo htmlspecialchars($prize['value']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-pagination"></div>
    </div>
</section>

<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new Swiper('.prizes-layout-container', {
        slidesPerView: 'auto',
        spaceBetween: 16,
        freeMode: true,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
    });
});
</script>