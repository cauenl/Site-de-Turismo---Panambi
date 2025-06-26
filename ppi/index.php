<?php
session_start();
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null; // Get user ID from session if logged in

// Verificar se o usuário é admin
$is_admin = false;
if ($usuario_id) {
    require_once 'php/config.php';
    require_once 'php/admin_functions.php';
    $is_admin = isAdmin($conexao, $usuario_id);
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Turismo</title>
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <div class="background">
        <!-- Header Styles Red Bar -->
        <header class="header-red-bar">
            <!-- header-content -->
            <div class="header-content">
                <div class="header-contact">
                    <a href="#">+55 55 8402-7519</a>
                    <a href="#">coord.turismo@panambi.rs.gov.br</a>
                </div>
                <div class="header-social" id="header">
                    <a href="https://www.facebook.com/" target="_blank" class="facebook"><svg
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="bi bi-facebook" viewBox="0 0 16 16">
                            <path
                                d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951" />
                        </svg></a>
                    <a href="https://www.instagram.com/desenvolvepanambi/" target="_blank" class="instagram"><svg
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="bi bi-instagram" viewBox="0 0 16 16">
                            <path
                                d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334" />
                        </svg></a>
                    <a href="https://www.twitter.com/" target="_blank" class="whatsapp"><svg
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="bi bi-whatsapp" viewBox="0 0 16 16">
                            <path
                                d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
                        </svg></a>
                    <?php if ($usuario_id): ?>
               </a>         <a href="php/logout.php" target="_self" class="login">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z" />
                                <path fill-rule="evenodd"
                                    d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z" />
                            </svg>
                        </a>
                    <?php else: ?>
                        <a href="./login.php" target="_self" class="login">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                class="bi bi-person-circle" viewBox="0 0 16 16">
                                <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                                <path fill-rule="evenodd"
                                    d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z" />
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <!-- nav-bar -->
            <nav class="nav-bar">
                <!-- header-bottom-nav -->
                <div class="header-bottom-nav">
                    <div class="header-logo">
                        <a href="./index.html"><img src="./img/escudo2.png" alt="logo"></a>
                    </div>
                    <ul class="menu">
                        <li class="nav-item"><a href="#header">Início</a></li>
                        <li class="nav-item"><a href="#historia">A Cidade</a></li>
                        <li class="nav-item"><a href="./Eventos.php">Eventos</a></li>
                        <li class="nav-item"><a href="#">Roteiros</a></li>
                        <li class="nav-item"><a href="#">Contato</a></li>
                        <?php if ($usuario_id): ?>
                            <li class="nav-item"><a href="./formEvent.php">Cadastrar Eventos</a></li>
                            <li class="nav-item"><a href="./formLocalTuristico.php">Cadastrar Local Turístico</a></li>
                            <?php if ($is_admin): ?>
                                <li class="nav-item"><a href="./admin.php" style="color: #ff6b6b; font-weight: bold;">Painel Admin</a></li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>

                    <div class="mobile-menu-icon">
                        <button onclick="menuShow()" class="mobile-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                class="bi bi-list" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </nav>

            <!-- Mobile Menu -->
            <div class="mobile-menu overlay-menu">
                <div class="close-btn">
                    <button class="close-menu">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="bi bi-x-lg" viewBox="0 0 16 16">
                            <path
                                d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z" />
                        </svg>
                    </button>
                </div>

                <ul>
                    <li class="nav-item"><a href="#header">Início</a></li>
                    <li class="nav-item"><a href="#">A Cidade</a></li>
                    <li class="nav-item"><a href="#">Experiências</a></li>
                    <li class="nav-item"><a href="#">Eventos</a></li>
                    <li class="nav-item"><a href="#">Roteiros</a></li>
                    <li class="nav-item"><a href="#">Contato</a></li>
                    <?php if ($usuario_id): ?>
                        <li><a href="./formEvent.php">Cadastrar Eventos</a></li>
                        <li><a href="./formLocalTuristico.php">Cadastrar Local Turístico</a></li>
                    <?php endif; ?>
                </ul>

                <div class="mobile-content">
                    <div class="mobile-contact">
                        <a href="#">+55 55 8402-7519</a>
                        <a href="#">coord.turismo@panambi.rs.gov.br</a>
                    </div>
                    <div class="mobile-social">
                        <a href="https://www.facebook.com/" target="_blank" class="facebook"><svg
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                class="bi bi-facebook" viewBox="0 0 16 16">
                                <path
                                    d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951" />
                            </svg></a>
                        <a href="https://www.instagram.com/desenvolvepanambi/" target="_blank" class="instagram"><svg
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                class="bi bi-instagram" viewBox="0 0 16 16">
                                <path
                                    d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334" />
                            </svg></a>
                        <a href="https://www.twitter.com/" target="_blank" class="whatsapp"><svg
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                class="bi bi-whatsapp" viewBox="0 0 16 16">
                                <path
                                    d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
                            </svg></a>
                        <a href="./login.html" target="_blank" class="login"><svg xmlns="http://www.w3.org/2000/svg"
                                width="24" height="24" fill="currentColor" class="bi bi-person-circle"
                                viewBox="0 0 16 16">
                                <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                                <path fill-rule="evenodd"
                                    d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1" />
                            </svg></a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Header Main Container -->
        <main class="header-container">
            <section class="wrap">

                <section class="header-slide">
                    <div class="slide-text">
                        <h2>Confira os locais que estão abertos em Panambi</h2>
                        <a href="#div-historia"><button class="historia-btn">Conheça</button></a>
                    </div>

                    <div class="slide-wrapper">
                        <div class="slide">
                            <img src="./img/xis_gaucho.jpg" alt='Xis Gaúcho'>
                            <p>Yes Burger</p>
                        </div>

                        <div class="slide">
                            <img src="./img/kasekuchen.jpg" alt="panambi">
                            <p>Padaria Nina</p>
                        </div>
                        <div class="slide">
                            <img src="./img/bendito_cafe.jpg" alt="panambi">
                            <p>Bendito Café</p>
                        </div>
                        <div class="slide">
                            <img src="./img/rada-Fionnabarra-Specialty-Coffee-beverage-2023-04-1.jpg" alt="panambi">
                            <p>Fionnabarra</p>
                        </div>
                        <div class="slide">
                            <img src="./img/barca_acai_bigmilk.jpg" alt="panambi">
                            <p>Big Milk</p>
                        </div>
                    </div>
                </section>
            </section>
        </main>
    </div>

    <main>
        <!-- Cards Section -->
        <section class="container-card">
            <div class="card-title">
                <h2>O que fazer em Panambi?</h2>
            </div>

            <div class="card-container">
                <!-- Rural Card -->
                <div class="card rural">
                    <img src="https://panambi.atende.net/atende.php?rot=49080&aca=105&processo=renderImage&parametro=%7B%22type%22%3A11%2C%22codigo%22%3A%2286F1C92FB5C2EA98A982777C2F7A81235556BE92%22%7D&cidade=padrao"
                        alt="rural">
                    <div class="card_content">
                        <h3>Turismo Rural</h3>
                        <p>Conheça os principais locais de turismo rural da cidade</p>
                        <a href="TurismoRural.php"  class="card-btn"> Saiba Mais</a>
                    </div>
                </div>

                <!-- Pontos Turismo Card -->
                <div class="card">
                    <img src="./img/igreja.jpg" alt="turismo">
                    <div class="card_content">
                        <h3>Pontos Turisticos</h3>
                        <p>Conheça os principais locais de turismo da cidade</p>
                        <a href="turismo.php"  class="card-btn"> Saiba Mais</a>
                    </div>
                </div>

                <!-- Gastronomia Card -->
                <div class="card">
                    <img src="https://www.noroesteonline.com/wp-content/uploads/2022/07/947c8e_44f9aef11677498e90281ed142a07864_mv2.webp"
                        alt="Gastronomia">
                    <div class="card_content">
                        <h3>Gastronomia</h3>
                        <p>Conheça os principais locais de gastronomia da cidade</p>
                        <a href="gastronomia.php" class="card-btn"> Saiba
                            Mais</a>
                    </div>
                </div>

                <!-- Hospedagem Card -->
                <div class="card">
                    <img src="https://cf.bstatic.com/xdata/images/hotel/max1024x768/134190713.jpg?k=f3401ca87c50c5683d36866a633d61f41a7b553231240715275f2d6e75a0439c&o=&hp=1"
                        alt="hospedagem">
                    <div class="card_content">
                        <h3>Hospedagem</h3>
                        <p>Conheça os principais locais de hospedagem</p>
                        <a href="hospedagem.php" class="card-btn"> Saiba
                            Mais</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Historia Section -->
    <main class="container-historia" id="historia">
        <div class="wrap">
            <div class="content-historia" id="div-historia">
                <div class="historia-title">
                    <h2>História de Panambi</h2>
                    <h3>Um pouco de informações</h3>
                </div>

                <div class="panambi-historia">
                    <div class="historia-top">
                        <span>
                            A povoação, de origem portuguesa teve início a partir de 1820 e a colonização, de
                            origem alemã, iniciou com a fundação da Colônia chamada "Neu-Württemberg". O Dr.
                            Hermann Meyer, em expedição realizada ao Mato Grosso, tomou conhecimento da
                            existência de terras férteis no Estado. Para promover os trabalhos de colonização,
                            mantev um administrador remunerado, o senhor Carlos Dhein, que lavrou a primeira
                            escritura da colônia para Dr. Meyer, em 31 de agosto de 1898. A colonização visava,
                            inicialmente, imigrantes vindos de Württemberg, na Alemanha, mas também famílias
                            vindas das antigas colônias da região de Estrela e de Santa Cruz do Sul ocuparam seu
                            espaço no local.
                            <br><br>
                            De 1898 até 1938, permaneceu a denominação de Neu-Württemberg para a colônia como um
                            todo. Com a demarcação da área urbana em 1901, recebeu a designação Elsenau, como
                            uma homenagem à esposa de Meyer, chamada Else. Em 1938 a colônia foi elevada à
                            categoria de Vila. A partir daí ainda houve mais três mudanças de nome: Pindorama
                            (1938), Tabapirã (1944), e, finalmente, Panambi, a partir de 29 de dezembro de 1944.
                        </span>
                    </div>

                    <div class="img-historia">
                        <img src="https://www.estado.rs.gov.br/upload/recortes/201802/26112259_1452279_GDO.jpg" alt="">
                    </div>
                </div>

                <div class="historia-bottom">
                    <span>
                        Contando com uma estrutura sócio-econômica bem desenvolvida, em 1949 pleiteou-se a
                        emancipação, após uma vasta campanha. Com vários conflitos e discordâncias, realizaram-se
                        dois plebiscitos, no período de 1949 e 1953, sendo que, no dia 15 de dezembro de 1954, foi
                        decretada a emancipação de Panambi e marcada a data para a primeira eleição para Prefeito e
                        para vereadores. Sua instalação oficial ocorreu em 28 de fevereiro de 1955. (Fonte: MAHP)
                    </span>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-social">
            <a href="https://www.facebook.com/" target="_blank" class="facebook"><svg xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16">
                    <path
                        d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951" />
                </svg></a>
            <a href="https://www.instagram.com/desenvolvepanambi/" target="_blank" class="instagram"><svg
                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                    class="bi bi-instagram" viewBox="0 0 16 16">
                    <path
                        d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334" />
                </svg></a>
            <a href="https://www.twitter.com/" target="_blank" class="whatsapp"><svg xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                    <path
                        d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
                </svg></a>
        </div>
        <div class="footer-items">
            <a href="#header">Início</a>
            <a href="#historia">A Cidade</a>
            <a href="#">Experiências</a>
            <a href="./Eventos.html">Eventos</a>
            <a href="#">Roteiros</a>
            <a href="#">Contato</a>
            <!--<a href="./formEvent.html">Cadastrar Evento</a>-->
        </div>

        <div class="footer-copyright">
            <span>Copyright © 2024 Portal de Turismo - Todos os direitos reservados</span>
        </div>
    </footer>
    <script src="./js/main.js" defer></script>
</body>

</html>

<?php
$result = null;
if ($result && $result->num_rows > 0) {
    session_start();
    $usuario = $result->fetch_assoc();
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['nome'] = $usuario['nome'];
    header('Location: index.php');
    exit;
}
?>