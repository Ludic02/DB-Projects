<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h3>
                <i class="fas fa-check-circle me-2"></i>
                Prenotazione Confermata!
            </h3>
        </div>
        <div class="card-body">
            <div class="alert alert-success">
                <h4>Grazie per il tuo acquisto!</h4>
                <p>La tua prenotazione è stata confermata e il pagamento è stato completato con successo.</p>
            </div>

            <div class="mt-4">
                <h4>Riepilogo Prenotazione</h4>
                <table class="table">
                    <tr>
                        <th>Treno:</th>
                        <td><?php echo htmlspecialchars($booking['nome_treno']); ?></td>
                    </tr>
                    <tr>
                        <th>Da:</th>
                        <td><?php echo htmlspecialchars($booking['stazione_partenza']); ?></td>
                    </tr>
                    <tr>
                        <th>A:</th>
                        <td><?php echo htmlspecialchars($booking['stazione_arrivo']); ?></td>
                    </tr>
                    <tr>
                        <th>Data:</th>
                        <td><?php echo date('d/m/Y', strtotime($booking['data_viaggio'])); ?></td>
                    </tr>
                    <tr>
                        <th>Posti:</th>
                        <td><?php echo $booking['numero_posti']; ?></td>
                    </tr>
                    <tr>
                        <th>Importo Pagato:</th>
                        <td>€ <?php echo number_format($booking['importo'], 2, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <th>Codice Prenotazione:</th>
                        <td><strong><?php echo $booking['id']; ?></strong></td>
                    </tr>
                </table>
            </div>

            <div class="mt-4">
                <button onclick="window.print()" class="btn btn-outline-primary">
                    <i class="fas fa-print me-2"></i>
                    Stampa Riepilogo
                </button>
                <a href="index.php" class="btn btn-primary ms-2">
                    <i class="fas fa-home me-2"></i>
                    Torna alla Homepage
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn {
        display: none;
    }
}
</style>