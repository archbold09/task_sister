function logIn(event) {
  event.preventDefault();

  let userName = document.getElementById("username");
  let password = document.getElementById("password");

  if(userName.value.length > 0 && password.value.length > 0 ){
    
  }else{
    swal({
        title: "Llene todos los campos",
        icon: "error",
        button: "Gracias!",
      });
  }
  
}
