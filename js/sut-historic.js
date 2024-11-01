function copyLink(element) {
    if (element) {
        document.getElementById('copy-button').classList.add('copied');
        document.getElementById('copied-txt').innerText = 'Copied';
        let textToCopy = document.getElementById(element).innerText;
        const el = document.createElement('textarea');
        el.value = 'https://byw.li/'+textToCopy;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        setTimeout(function(){
            document.getElementById('copy-button').classList.remove('copied');
            document.getElementById('copied-txt').innerText = 'Copy';
        }, 2000);

    }
}