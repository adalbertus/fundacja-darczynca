import Clipboard from 'stimulus-clipboard'

export default class extends Clipboard {
    connect() {
        super.connect();
        this.successContentValue = 'Skopiowane!!';
        this.successContentValue = '<i class="fa-solid fa-check"></i>';
    }

    // Function to override on copy.
    copy(event) {
        event.preventDefault()

        const text = this.sourceTarget.href || this.sourceTarget.innerHTML || this.sourceTarget.value

        navigator.clipboard.writeText(text).then(() => this.copied())
    }

    // Function to override when to input is copied.
    //   copied() {
    //
    //   }

    // copied() {
    //     if (!this.hasButtonTarget) return

    //     if (this.timeout) {
    //         clearTimeout(this.timeout)
    //     }

    //     this.buttonTarget.innerHTML = this.successContentValue

    //     this.timeout = setTimeout(() => {
    //         this.buttonTarget.innerHTML = this.originalContent
    //     }, this.successDurationValue)
    // }
}