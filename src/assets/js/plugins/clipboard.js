export function install(Vue) {
    Vue.prototype.$copyToCliboard = (text, rmpl = false) => {
        if(rmpl) try { text = text.replace('+7', '') } catch (err) {}

        let textArea = document.createElement("textarea");
        textArea.style.position = 'fixed';
        textArea.style.padding = textArea.style.left = textArea.style.top = 0;
        textArea.style.height = textArea.style.width = '2em';
        textArea.style.boxShadow = textArea.style.outline = textArea.style.border = 'none';
        textArea.style.background = 'transparent';

        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            let successful = document.execCommand('copy');
            let msg = successful ? 'successful' : 'unsuccessful';
            Vue.ntf.success('Скопировано в буфер обмена');
            console.log('Copying text command was ' + msg);
        } catch (err) {
            console.log('Oops, unable to copy');
            Vue.ntf.error('Что-то пошло не так');
        }
        document.body.removeChild(textArea);
    };
}

