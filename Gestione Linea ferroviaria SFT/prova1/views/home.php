<div class="container mt-4">
    <?php if(Session::isLoggedIn() && Session::getUserType() === 'esercente'): ?>
        <?php require 'views/merchant/dashboard.php'; ?>
    <?php else: ?>
        <!-- Banner principale -->
        <div class="jumbotron">
            <h1 class="display-4">Benvenuti sulla Ferrovia Turistica</h1>
            <p class="lead">Scopri il fascino del viaggio su treni storici attraverso paesaggi mozzafiato.</p>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-8">
                    <p>54 km di pura magia, 10 stazioni storiche, un'esperienza indimenticabile.</p>
                    <a class="btn btn-primary btn-lg" href="index.php?page=trains" role="button">Consulta gli orari</a>
                    <?php if(!Session::isLoggedIn()): ?>
                        <a class="btn btn-outline-primary btn-lg ms-2" href="index.php?page=auth&action=register" role="button">
                            Registrati per prenotare
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sezione PaySteam e Informativa -->
        <div class="row mt-4">
            <?php if(Session::isLoggedIn()): ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Il tuo conto PaySteam</h3>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5>Saldo disponibile</h5>
                                    <h2 class="text-primary">€<?php echo number_format(Session::getUserBalance(), 2, ',', '.'); ?></h2>
                                </div>
                                <div class="col-auto">
                                    <a href="index.php?page=pay" class="btn btn-primary">
                                        <i class="fas fa-wallet me-2"></i>
                                        Gestisci Conto
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title mb-0">PaySteam - Il nostro sistema di pagamento</h3>
                        </div>
                        <div class="card-body">
                            <h5>Perché scegliere PaySteam?</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Pagamenti sicuri e veloci</li>
                                <li><i class="fas fa-check text-success me-2"></i>Gestione saldo personale</li>
                                <li><i class="fas fa-check text-success me-2"></i>Carte di credito memorizzate</li>
                                <li><i class="fas fa-check text-success me-2"></i>Storico transazioni</li>
                            </ul>
                            <div class="mt-3">
                                <a href="index.php?page=auth&action=register" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Registrati ora
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Prossimi treni</h3>
                    </div>
                    <div class="card-body">
                        <p>Consulta gli orari e prenota il tuo viaggio sulla nostra linea storica.</p>
                        <a href="index.php?page=trains" class="btn btn-primary">
                            <i class="fas fa-train me-2"></i>
                            Vedi orari
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if(Session::hasMessage()): ?>
        <div class="alert <?php echo Session::getErrorMessage() ? 'alert-danger' : 'alert-success'; ?> alert-dismissible fade show mt-3">
            <?php echo Session::getErrorMessage() ?: Session::getSuccessMessage(); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>


    <div class="card border-info mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Credenziali applicative</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Profilo</th>
                            <th>Email</th>
                            <th>Password</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Admin</td>
                            <td>admin@sft.it</td>
                            <td>Password123!</td>
                        </tr>
                        <tr>
                            <td>Esercizio</td>
                            <td>esercizio@sft.it</td>
                            <td>Password123!</td>
                        </tr>
                        <tr>
                            <td>Esercente</td>
                            <td>merchant@sft.it</td>
                            <td>Password123!</td>
                        </tr>
                        <tr>
                            <td>Utente Demo</td>
                            <td>utente@esempio.it</td>
                            <td>Password123!</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <small class="text-muted">Database: lu.dicampli - Password: jnBpp2f9</small>
        </div>
    </div>
</div>