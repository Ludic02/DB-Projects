<!DOCTYPE html>
<html>
<head>
    <title>PaySteam - Ricarica</title>
    <link rel="stylesheet" href="assets/css/pay.css">
    <style>
        .ricarica-form {
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-actions {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="pay-dashboard">
        <div class="ricarica-form">
            <h2>Ricarica conto</h2>

            <?php if (Session::hasMessage()): ?>
                <div class="alert <?php echo Session::getErrorMessage() ? 'alert-danger' : 'alert-success'; ?>">
                    <?php echo Session::getErrorMessage() ?: Session::getSuccessMessage(); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($cards)): ?>
                <div class="alert alert-warning">
                    Non hai carte registrate. 
                    <a href="index.php?page=pay&action=addCard">Aggiungi una carta</a> per effettuare una ricarica.
                </div>
            <?php else: ?>
                <form action="index.php?page=pay&action=ricarica" method="POST">
                    <div class="form-group">
                        <label for="importo">Importo da ricaricare (€)</label>
                        <input type="number" id="importo" name="importo" 
                               min="1" step="0.01" required
                               placeholder="10.00">
                    </div>

                    <div class="form-group">
                        <label for="card_id">Seleziona carta</label>
                        <select id="card_id" name="card_id" required>
                            <option value="">Scegli una carta</option>
                            <?php foreach ($cards as $card): ?>
                                <option value="<?php echo $card['id']; ?>">
                                    <?php echo htmlspecialchars($card['numero_mascherato']); ?> - 
                                    Scad. <?php echo htmlspecialchars($card['scadenza']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <a href="index.php?page=pay" class="action-button danger">Annulla</a>
                        <button type="submit" class="action-button">Ricarica</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>