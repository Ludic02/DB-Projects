<div class="container mt-4">
    <?php 
    $errorMessage = Session::getErrorMessage();
    if ($errorMessage): 
    ?>
        <div class="alert alert-danger">
            <?php echo $errorMessage; ?>
        </div>
    <?php endif; ?>

    <h2>Autorizza Transazione</h2>
    <div class="card mt-4">
        <div class="card-body">
            <form action="index.php?page=merchant&action=withdraw" method="post">
                <div class="mb-3">
                    <label for="amount" class="form-label">Importo</label>
                    <div class="input-group">
                        <span class="input-group-text">€</span>
                        <input type="number" 
                               class="form-control" 
                               id="amount" 
                               name="amount" 
                               min="0" 
                               step="0.01" 
                               required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Causale</label>
                    <input type="text" 
                           class="form-control" 
                           id="description" 
                           name="description" 
                           required>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Autorizza Transazione</button>
                    <a href="index.php?page=merchant" class="btn btn-secondary">Annulla</a>
                </div>
            </form>
        </div>
    </div>
</div>