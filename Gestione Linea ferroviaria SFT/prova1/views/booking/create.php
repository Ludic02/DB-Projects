<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Nuova Prenotazione</h2>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (empty($trains)): ?>
        <div class="alert alert-warning">
            Non ci sono treni disponibili al momento.
        </div>
    <?php elseif (empty($stations)): ?>
        <div class="alert alert-warning">
            Non ci sono stazioni disponibili al momento.
        </div>
    <?php else: ?>
        <form action="/index.php?route=bookings/create" method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="train_id" class="form-label">Seleziona Treno</label>
                <select name="train_id" id="train_id" class="form-control" required>
                    <option value="">Seleziona un treno</option>
                    <?php foreach ($trains as $train): ?>
                        <option value="<?php echo $train['id']; ?>">
                            <?php echo htmlspecialchars($train['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">
                    Seleziona un treno
                </div>
            </div>

            <div class="mb-3">
                <label for="station_from" class="form-label">Stazione di Partenza</label>
                <select name="station_from" id="station_from" class="form-control" required>
                    <option value="">Seleziona stazione di partenza</option>
                    <?php foreach ($stations as $station): ?>
                        <option value="<?php echo $station['id']; ?>">
                            <?php echo htmlspecialchars($station['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">
                    Seleziona la stazione di partenza
                </div>
            </div>

            <div class="mb-3">
                <label for="station_to" class="form-label">Stazione di Arrivo</label>
                <select name="station_to" id="station_to" class="form-control" required>
                    <option value="">Seleziona stazione di arrivo</option>
                    <?php foreach ($stations as $station): ?>
                        <option value="<?php echo $station['id']; ?>">
                            <?php echo htmlspecialchars($station['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">
                    Seleziona la stazione di arrivo
                </div>
            </div>

            <div class="mb-3">
                <label for="booking_date" class="form-label">Data del Viaggio</label>
                <input type="date" name="booking_date" id="booking_date" 
                       class="form-control" required 
                       min="<?php echo date('Y-m-d'); ?>"
                       value="<?php echo date('Y-m-d'); ?>">
                <div class="invalid-feedback">
                    Seleziona una data valida
                </div>
            </div>

            <div class="mb-3">
                <label for="seats" class="form-label">Numero Posti</label>
                <input type="number" name="seats" id="seats" 
                       class="form-control" required 
                       min="1" max="10" value="1">
                <div class="invalid-feedback">
                    Seleziona un numero di posti valido (1-10)
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Prenota</button>
            <a href="/index.php?route=bookings/list" class="btn btn-secondary">Annulla</a>
        </form>
    <?php endif; ?>
</div>

<!-- Validazione form lato client -->
<script>
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
})()
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>