<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3>Conferma Pagamento</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <h4>Riepilogo Transazione</h4>
                <p><strong>Descrizione:</strong> <?php echo htmlspecialchars($paysteam_data['descrizione']); ?></p>
                <p><strong>Importo:</strong> € <?php echo number_format($paysteam_data['prezzo'], 2, ',', '.'); ?></p>
                <p><strong>ID Transazione:</strong> <?php echo htmlspecialchars($paysteam_data['id_transazione']); ?></p>
            </div>

            <div class="mb-4">
                <h4>Autorizzazione Pagamento</h4>
                <p>Confermi di voler procedere con il pagamento?</p>

                <!-- Form per simulare la risposta di PaySteam -->
                <form action="index.php?page=api&action=payment_response" method="GET" class="mt-3">
                    <input type="hidden" name="id_transazione" value="<?php echo htmlspecialchars($paysteam_data['id_transazione']); ?>">
                    <input type="hidden" name="url_inviante" value="<?php echo htmlspecialchars($paysteam_data['url_inviante']); ?>">
                    
                    <div class="mb-3">
                        <select name="esito" class="form-select">
                            <option value="OK">Pagamento riuscito</option>
                            <option value="KO">Pagamento fallito</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Conferma Pagamento</button>
                    <a href="index.php?page=bookings" class="btn btn-secondary ms-2">Annulla</a>
                </form>
            </div>
        </div>
    </div>
</div>