const button = document.querySelector('#create-campaign-button');

const disableButton = () => {
    var a = document.forms["Form"]["name"].value;
    var b = document.forms["Form"]["description"].value;
    if (!a || !b) {
      alert("Please Fill All Required Fields");
      return false;
    } else {
        button.disabled = true;
        document.getElementById('loader-generate').classList.add('active');
        button.classList.add('active');
        document.getElementById("create-campaign-form").submit();
    }

};
if (button) {
    button.addEventListener('click', disableButton);
}