<?php
if (!isset($prenotazione)) {
    header('Location: index.php?page=trains');
    exit;
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Pagamento prenotazione</h3>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5>Dettagli prenotazione:</h5>
                        <dl class="row">
                            <dt class="col-sm-4">Treno</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($prenotazione['treno_nome']); ?></dd>

                            <dt class="col-sm-4">Da</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($prenotazione['stazione_partenza']); ?></dd>

                            <dt class="col-sm-4">A</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($prenotazione['stazione_arrivo']); ?></dd>

                            <dt class="col-sm-4">Data</dt>
                            <dd class="col-sm-8"><?php echo date('d/m/Y', strtotime($prenotazione['data_viaggio'])); ?></dd>

                            <dt class="col-sm-4">Posti</dt>
                            <dd class="col-sm-8"><?php echo $prenotazione['numero_posti']; ?></dd>
                        </dl>
                    </div>

                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h5 class="mb-0">Saldo disponibile</h5>
                                </div>
                                <div class="col-md-6 text-end">
                                    <h3 class="mb-0">€ <?php echo number_format($prenotazione['saldo_disponibile'], 2, ',', '.'); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h5 class="mb-0">Importo da pagare</h5>
                                </div>
                                <div class="col-md-6 text-end">
                                    <h3 class="text-primary mb-0">€ <?php echo number_format($prenotazione['importo'], 2, ',', '.'); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($prenotazione['saldo_disponibile'] < $prenotazione['importo']): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            Saldo insufficiente. <a href="index.php?page=pay&action=ricarica">Ricarica il tuo conto</a>
                        </div>
                    <?php else: ?>
                        <form action="index.php?page=payment&action=process" method="POST">
                            <input type="hidden" name="booking_id" value="<?php echo $prenotazione['id']; ?>">
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check-circle"></i>
                                    Conferma pagamento
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>

                    <div class="text-center mt-3">
                        <a href="index.php?page=trains" class="btn btn-link">
                            <i class="fas fa-arrow-left"></i>
                            Torna agli orari
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>