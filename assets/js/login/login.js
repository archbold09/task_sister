function logIn(event) {
  event.preventDefault();

  let userName = document.getElementById("username");
  let password = document.getElementById("password");

  if (userName.value.length > 0 && password.value.length > 0) {
    (async () => {
      try {
        const URL = "../../controllers/usersControllers.php";
        const formData = new FormData();
        formData.append("userName", userName.value);
        formData.append("password", password.value);
        formData.append("petition", "logIn");
        const config = {
          method: "POST",
          body: formData,
        };
        const response = await fetch(URL, config);
        const data = await response.json();
        if (data.state) {
          swal({
            title: "Iniciando sesión",
            icon: "success",
          });
          setTimeout(() => {
            window.location.href = '../user/index.php'
          }, 2000);
        } else {
          swal({
            title: "Error al iniciar sesión",
            icon: "error",
            button: "Gracias!",
          });
        }
      } catch (error) {
        console.log(`Error: ${error}`);
      }
    })();
  } else {
    swal({
      title: "Llene todos los campos",
      icon: "error",
      button: "Gracias!",
    });
  }
}
