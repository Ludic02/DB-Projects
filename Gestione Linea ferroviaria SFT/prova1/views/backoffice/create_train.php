<h2>Crea Nuovo Treno</h2>

<form method="POST" action="index.php?page=backoffice&action=createTrain">
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Data</label>
                        <input type="date" name="data" class="form-control" required
                               min="<?php echo date('Y-m-d'); ?>"
                               max="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Direzione</label>
                        <select name="direzione" class="form-select" required>
                            <option value="andata">Andata</option>
                            <option value="ritorno">Ritorno</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Locomotiva</label>
                <select name="locomotiva_id" class="form-select" required>
                    <option value="">Seleziona locomotiva</option>
                    <?php foreach($materiale as $m): ?>
                        <?php if($m['tipo'] === 'locomotiva'): ?>
                            <option value="<?php echo $m['id']; ?>">
                                <?php echo htmlspecialchars($m['codice']); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Carrozze</label>
                <?php foreach($materiale as $m): ?>
                    <?php if($m['tipo'] === 'carrozza'): ?>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" 
                                   name="carrozze[]" value="<?php echo $m['id']; ?>">
                            <label class="form-check-label">
                                <?php echo htmlspecialchars($m['codice']); ?> 
                                (<?php echo $m['posti_sedere']; ?> posti)
                            </label>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <h5 class="mt-4">Orari</h5>
            <?php 
            $stazioni = [
                ['id' => 1, 'nome' => 'Torre Spaventa'],
                ['id' => 10, 'nome' => 'Villa San Felice']
            ];
            ?>
            <?php foreach($stazioni as $i => $stazione): ?>
                <div class="mb-3">
                    <label class="form-label">
                        <?php echo htmlspecialchars($stazione['nome']); ?>
                    </label>
                    <input type="time" name="orari[<?php echo $stazione['id']; ?>]" 
                           class="form-control" required>
                    <input type="hidden" name="stazioni[]" value="<?php echo $stazione['id']; ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Crea Treno</button>
    <a href="index.php?page=backoffice" class="btn btn-secondary">Annulla</a>
</form>