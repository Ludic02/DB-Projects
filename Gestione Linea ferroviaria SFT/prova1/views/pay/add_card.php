<!DOCTYPE html>
<html>
<head>
    <title>PaySteam - Aggiungi Carta</title>
    <link rel="stylesheet" href="assets/css/pay.css">
    <style>
        .card-form {
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

        .form-group input {
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
        <div class="card-form">
            <h2>Aggiungi nuova carta</h2>

            <?php if (Session::hasMessage()): ?>
                <div class="alert <?php echo Session::getErrorMessage() ? 'alert-danger' : 'alert-success'; ?>">
                    <?php echo Session::getErrorMessage() ?: Session::getSuccessMessage(); ?>
                </div>
            <?php endif; ?>

            <form action="index.php?page=pay&action=saveCard" method="POST">
                <div class="form-group">
                    <label for="numero">Numero carta</label>
                    <input type="text" id="numero" name="numero" required
                           pattern="\d{16}" maxlength="16"
                           placeholder="1234567890123456">
                    <small>Inserisci le 16 cifre senza spazi</small>
                </div>

                <div class="form-group">
                    <label for="scadenza">Scadenza (MM/YY)</label>
                    <input type="text" id="scadenza" name="scadenza" required
                           pattern="\d{2}/\d{2}" maxlength="5"
                           placeholder="12/25">
                </div>

                <div class="form-group">
                    <label for="cvv">CVV</label>
                    <input type="text" id="cvv" name="cvv" required
                           pattern="\d{3}" maxlength="3"
                           placeholder="123">
                </div>

                <div class="form-actions">
                    <a href="index.php?page=pay" class="action-button danger">Annulla</a>
                    <button type="submit" class="action-button">Salva carta</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('scadenza').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substr(0, 2) + '/' + value.substr(2);
            }
            e.target.value = value;
        });
    </script>
</body>
</html>