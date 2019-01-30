// Variables
var signupButton = document.getElementById('signup-button'),
    loginButton = document.getElementById('login-button'),
    userForms = document.getElementById('user_options-forms')


// Add event listener to the "Sign Up" button
signupButton.addEventListener('click', () => {
  userForms.classList.remove('login-click')
  userForms.classList.add('signup-click')
}, false)


// Add event listener to the "Login" button
loginButton.addEventListener('click', () => {
  userForms.classList.remove('signup-click')
  userForms.classList.add('login-click')
}, false)

function sendVerificationCode(username,el){
  if(el===undefined){
    el='.auth-logs';
  }
  axios.post('/user/verify/'+username, {
    token:"bad47df23cb7e6b3b8abf68cbba85d0f"
  })
  .then(function (response) {
    
      if(response.data.status){
        document.querySelector(el).innerHTML="<p class='is--success'>"+"لینک فعال سازی به ایمیل شما ارسال شد. درصورت مشاهده نکردن ایمیل پوشه spam خود را چک کنید. <a style='cursor:pointer;color:#5842d4' onclick='sendVerificationCode(\"coderguy\",\""+el+"\")'>ارسال مجدد</a>"+"</p>";
      }

  })
  .catch(function (error) {
    console.log(error);
  });
}