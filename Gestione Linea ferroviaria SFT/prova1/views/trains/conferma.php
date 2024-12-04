<?php if (!Session::isLoggedIn()) {
    header('Location: index.php?page=auth&action=login');
    exit;
} ?>

<div class="container">
    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-check-circle"></i>
                        Prenotazione confermata
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle"></i>
                        La tua prenotazione è stata registrata con successo!
                    </div>

                    <h4 class="mb-4">Riepilogo prenotazione #<?php echo $prenotazione['id']; ?></h4>

                    <!-- Dettagli del treno -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Dettagli treno</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <dl>
                                        <dt>Treno</dt>
                                        <dd><?php echo htmlspecialchars($prenotazione['treno_nome']); ?></dd>

                                        <dt>Tipo</dt>
                                        <dd><?php echo htmlspecialchars($prenotazione['treno_tipo']); ?></dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl>
                                        <dt>Data viaggio</dt>
                                        <dd><?php echo date('d/m/Y', strtotime($prenotazione['data_viaggio'])); ?></dd>

                                        <dt>Numero posti</dt>
                                        <dd><?php echo $prenotazione['numero_posti']; ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dettagli percorso -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Percorso</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Partenza</h6>
                                    <p class="mb-0">
                                        <strong><?php echo htmlspecialchars($prenotazione['stazione_partenza']); ?></strong><br>
                                        Km <?php echo $prenotazione['km_partenza']; ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Arrivo</h6>
                                    <p class="mb-0">
                                        <strong><?php echo htmlspecialchars($prenotazione['stazione_arrivo']); ?></strong><br>
                                        Km <?php echo $prenotazione['km_arrivo']; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Riepilogo costi -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Dettagli pagamento</h5>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <p class="mb-0">Totale da pagare</p>
                                </div>
                                <div class="col-md-6 text-end">
                                    <h3 class="text-primary mb-0">€ <?php echo number_format($costo_totale, 2, ',', '.'); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pulsanti azione -->
                    <div class="text-center mt-4">
                        <?php if ($prenotazione['stato_pagamento'] === 'IN_ATTESA'): ?>
                            <form action="index.php?page=payment&action=checkout" method="POST">
                                <input type="hidden" name="booking_id" value="<?php echo $prenotazione['id']; ?>">
                                <input type="hidden" name="amount" value="<?php echo $costo_totale; ?>">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-credit-card"></i>
                                    Procedi al pagamento
                                </button>
                            </form>
                        <?php endif; ?>

                        <a href="index.php?page=trains" class="btn btn-link mt-3">
                            <i class="fas fa-arrow-left"></i>
                            Torna agli orari
                        </a>
                        
                        <a href="index.php?page=bookings" class="btn btn-link mt-3">
                            <i class="fas fa-list"></i>
                            Le mie prenotazioni
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>