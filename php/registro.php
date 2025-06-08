<!-- Formulario para registrar un nuevo usuario -->
<form action="../php/registro_action.php" method="POST">
    <!-- Campo para ingresar el nombre de usuario -->
    <label for="userid">Usuario:</label>
    <input type="text" id="userid" name="userid" required>
    <br>
    <!-- Campo para ingresar la contraseña -->
    <label for="password">Contraseña:</label>
    <input type="password" id="password" name="password" required>
    <br>
    <!-- Botón para enviar el formulario -->
    <input type="submit" value="Registrar">
</form>
