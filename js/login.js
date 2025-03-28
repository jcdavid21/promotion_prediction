$(document).ready(()=>{
    $('#login').click((e)=>{
        e.preventDefault()
        $username = $('#username').val();
        $password = $('#password').val();

        console.log($username
        + " " + $password);

        if($username == "" || $password == ""){
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please fill all fields!',
            })
            return;
        }

        $.ajax({
            url: './backend/accounts/login.php',
            type: 'POST',
            data: {
                username: $username,
                password: $password
            },
            success: (data)=>{
                if(data == 'success'){
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Login successful!',
                    }).then(()=>{
                        window.location.href = "./components/dashboard.php";
                    })
                }else{
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Invalid username or password!',
                    })
                }
            }
        })
    })
})

