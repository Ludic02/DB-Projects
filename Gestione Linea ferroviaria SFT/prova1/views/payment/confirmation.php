<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h3 class="mb-0">
                <i class="fas fa-check-circle me-2"></i>
                Pagamento Completato con Successo
            </h3>
        </div>
        <div class="card-body">
            <div class="alert alert-success mb-4">
                <h4 class="alert-heading">Grazie per il tuo acquisto!</h4>
                <p>Il tuo biglietto è stato emesso e confermato.</p>
            </div>

            <!-- Dettagli Biglietto -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Il tuo Biglietto</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Codice Biglietto</h5>
                            <p class="h3 text-primary"><?php echo $ticket['codice_biglietto']; ?></p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h5>Data Acquisto</h5>
                            <p><?php echo date('d/m/Y H:i', strtotime($ticket['data_pagamento'])); ?></p>
                        </div>
                    </div>

                    <hr>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h5>Dettagli Viaggio</h5>
                            <table class="table">
                                <tr>
                                    <th>Treno:</th>
                                    <td><?php echo htmlspecialchars($ticket['nome_treno']); ?></td>
                                </tr>
                                <tr>
                                    <th>Data:</th>
                                    <td><?php echo date('d/m/Y', strtotime($ticket['data_viaggio'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Da:</th>
                                    <td>
                                        <?php echo htmlspecialchars($ticket['stazione_partenza']); ?>
                                        <small class="text-muted">(Km <?php echo number_format($ticket['km_partenza'], 3, ',', '.'); ?>)</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>A:</th>
                                    <td>
                                        <?php echo htmlspecialchars($ticket['stazione_arrivo']); ?>
                                        <small class="text-muted">(Km <?php echo number_format($ticket['km_arrivo'], 3, ',', '.'); ?>)</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Posti:</th>
                                    <td><?php echo $ticket['numero_posti']; ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Dettagli Pagamento</h5>
                            <table class="table">
                                <tr>
                                    <th>Importo Pagato:</th>
                                    <td>€ <?php echo number_format($ticket['importo'], 2, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <th>Codice Transazione:</th>
                                    <td><small><?php echo $ticket['codice_transazione']; ?></small></td>
                                </tr>
                                <tr>
                                    <th>Stato:</th>
                                    <td><span class="badge bg-success">Confermato</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Azioni -->
            
                <div>
                    <button onclick="window.print()" class="btn btn-outline-primary me-2">
                        <i class="fas fa-print me-2"></i>
                        Stampa Biglietto
                    </button>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>
                        Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .navbar, footer {
        display: none !important;
    }
    .card {
        border: none !important;
    }
}
</style>
