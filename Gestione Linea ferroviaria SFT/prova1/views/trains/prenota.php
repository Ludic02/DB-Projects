<?php
if (!isset($train) || !isset($stazioni)) {
    Session::setErrorMessage("Dati mancanti per la prenotazione");
    header('Location: index.php?page=trains');
    exit;
}
?>

<div class="container">
    <h2 class="mb-4">Prenota biglietto</h2>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title mb-0">Dettagli treno</h3>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Treno</dt>
                <dd class="col-sm-9"><?php echo htmlspecialchars($train['nome']); ?></dd>

                <dt class="col-sm-3">Tipo</dt>
                <dd class="col-sm-9"><?php echo htmlspecialchars($train['tipo']); ?></dd>

                <dt class="col-sm-3">Posti totali</dt>
                <dd class="col-sm-9"><?php echo htmlspecialchars($train['posti_totali']); ?></dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Dettagli prenotazione</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="index.php?page=trains&action=prenota_conferma" class="needs-validation" novalidate>
                <input type="hidden" name="treno_id" value="<?php echo $train['id']; ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="stazione_partenza" class="form-label">Stazione di partenza</label>
                        <select class="form-select" id="stazione_partenza" name="stazione_partenza" required>
                            <option value="">Seleziona stazione di partenza</option>
                            <?php foreach ($stazioni as $stazione): ?>
                                <option value="<?php echo $stazione['id']; ?>">
                                    <?php echo htmlspecialchars($stazione['nome']); ?> (Km <?php echo $stazione['km']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            Seleziona la stazione di partenza
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="stazione_arrivo" class="form-label">Stazione di arrivo</label>
                        <select class="form-select" id="stazione_arrivo" name="stazione_arrivo" required>
                            <option value="">Seleziona stazione di arrivo</option>
                            <?php foreach ($stazioni as $stazione): ?>
                                <option value="<?php echo $stazione['id']; ?>">
                                    <?php echo htmlspecialchars($stazione['nome']); ?> (Km <?php echo $stazione['km']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            Seleziona la stazione di arrivo
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="data_viaggio" class="form-label">Data del viaggio</label>
                        <input type="date" class="form-control" id="data_viaggio" name="data_viaggio" 
                               min="<?php echo date('Y-m-d'); ?>" required>
                        <div class="invalid-feedback">
                            Seleziona una data valida
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="numero_posti" class="form-label">Numero di posti</label>
                        <input type="number" class="form-control" id="numero_posti" name="numero_posti" 
                               min="1" max="10" value="1" required>
                        <div class="invalid-feedback">
                            Inserisci un numero di posti valido (1-10)
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-text">
                        Il costo del biglietto verrà calcolato in base alla distanza tra le stazioni.
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="index.php?page=trains" class="btn btn-secondary me-md-2">Annulla</a>
                    <button type="submit" class="btn btn-primary">Procedi al pagamento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validazione form lato client
(function () {
    'use strict';
    
    var forms = document.querySelectorAll('.needs-validation');
    
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            // Controlli custom
            var partenza = document.getElementById('stazione_partenza').value;
            var arrivo = document.getElementById('stazione_arrivo').value;
            
            if (partenza === arrivo) {
                event.preventDefault();
                alert('Le stazioni di partenza e arrivo devono essere diverse');
                return false;
            }
            
            form.classList.add('was-validated');
        }, false);
    });
})();

// Imposta la data minima al giorno corrente
document.getElementById('data_viaggio').min = new Date().toISOString().split('T')[0];
</script>