<?php
// Incluir el header
include 'includes/header.php';

// Datos de la organización
$mision = "En Patitas Seguras, nuestra misión es proporcionar un refugio seguro y amoroso para animales abandonados y maltratados, trabajando incansablemente para encontrar hogares permanentes y responsables para cada uno de ellos. Nos comprometemos a promover la tenencia responsable de mascotas a través de programas educativos, campañas de esterilización y concienciación sobre el bienestar animal.";

$vision = "Nuestra visión es crear una comunidad donde ningún animal sufra abandono o maltrato, y donde cada mascota tenga la oportunidad de vivir una vida plena y feliz en un hogar amoroso. Aspiramos a ser el referente en protección animal, expandiendo nuestro alcance y colaborando con instituciones para implementar políticas públicas que garanticen el bienestar de todos los animales.";

$valores = [
    "Compasión" => "Actuamos con empatía y respeto hacia todos los seres vivos.",
    "Compromiso" => "Dedicamos nuestros esfuerzos incansablemente al bienestar animal.",
    "Transparencia" => "Mantenemos honestidad y claridad en todas nuestras acciones.",
    "Responsabilidad" => "Promovemos la tenencia responsable y el cuidado adecuado.",
    "Colaboración" => "Trabajamos en equipo con la comunidad y otras organizaciones."
];
?>

<section class="mision-vision">
    <h2>Nuestra Misión</h2>
    <p><?php echo $mision; ?></p>
    
    <h2>Nuestra Visión</h2>
    <p><?php echo $vision; ?></p>
    
    <h2>Nuestros Valores</h2>
    <div class="valores-container">
        <?php foreach($valores as $valor => $descripcion): ?>
            <div class="valor-item">
                <h3><?php echo $valor; ?></h3>
                <p><?php echo $descripcion; ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<style>
.mision-vision {
    padding: 12rem 5% 6rem;
    text-align: center;
    background: #f9f9f9;
    min-height: 100vh;
}

.mision-vision h2 {
    color: #4aa6b5;
    font-size: 4rem;
    margin-bottom: 2rem;
    text-transform: uppercase;
}

.mision-vision p {
    font-size: 2.2rem;
    color: #446468;
    line-height: 1.6;
    max-width: 80rem;
    margin: 0 auto 6rem;
    padding: 3rem;
    background: white;
    border-radius: 2rem;
    box-shadow: 0 0 1.5rem rgba(0,0,0,0.1);
    border-left: 5px solid #df9d40;
}

.valores-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 3rem;
    max-width: 1200px;
    margin: 0 auto;
}

.valor-item {
    background: white;
    padding: 3rem;
    border-radius: 2rem;
    box-shadow: 0 0 1.5rem rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.valor-item:hover {
    transform: translateY(-10px);
}

.valor-item h3 {
    color: #4aa6b5;
    font-size: 2.8rem;
    margin-bottom: 1.5rem;
    border-bottom: 3px solid #df9d40;
    padding-bottom: 1rem;
}

.valor-item p {
    font-size: 1.8rem;
    color: #666;
    margin: 0;
    padding: 0;
    background: none;
    box-shadow: none;
    border: none;
}
</style>

<?php include 'includes/footer.php'; ?>