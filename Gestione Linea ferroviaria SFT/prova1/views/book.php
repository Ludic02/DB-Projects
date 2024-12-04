<?php
// File: views/trains/book.php
?>
<div class="container">
    <h2>Prenota Biglietto</h2>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if(isset($train) && $train): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h3>Dettagli Viaggio</h3>
            </div>
            <div class="card-body">
                <p><strong>Data:</strong> <?php echo date('d/m/Y', strtotime($train['data'])); ?></p>
                <p><strong>Treno:</strong> <?php echo $train['id']; ?></p>
                <p><strong>Partenza:</strong> <?php echo htmlspecialchars($train['stazione_partenza']); ?> 
                   alle <?php echo substr($train['partenza'], 0, 5); ?></p>
                <p><strong>Arrivo:</strong> <?php echo htmlspecialchars($train['stazione_arrivo']); ?> 
                   alle <?php echo substr($train['arrivo'], 0, 5); ?></p>
                
                <?php
                $posti_occupati = isset($train['posti_occupati']) ? $train['posti_occupati'] : 0;
                $posti_disponibili = $train['posti_totali'] - $posti_occupati;
                ?>
                <p><strong>Posti disponibili:</strong> <?php echo $posti_disponibili; ?></p>
            </div>
        </div>

        <?php if($posti_disponibili > 0): ?>
            <form method="POST">
                <div class="form-group mb-3">
                    <label>Numero di posti da prenotare:</label>
                    <select name="numero_posti" class="form-control">
                        <?php for($i = 1; $i <= min(4, $posti_disponibili); $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> posto/i</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Conferma Prenotazione</button>
                <a href="index.php?page=trains" class="btn btn-secondary">Annulla</a>
            </form>
        <?php else: ?>
            <div class="alert alert-warning">
                Mi dispiace, non ci sono posti disponibili per questo treno.
            </div>
            <a href="index.php?page=trains" class="btn btn-primary">Torna all'elenco treni</a>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-danger">Treno non trovato</div>
        <a href="index.php?page=trains" class="btn btn-primary">Torna all'elenco treni</a>
    <?php endif; ?>
</div>