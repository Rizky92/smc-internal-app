@component('mail::message')
    # Hasil MCU Lorem ipsum dolor sit, amet consectetur adipisicing elit. Blanditiis non aut voluptatem, quibusdam ipsa optio. Lorem ipsum dolor sit amet consectetur, adipisicing elit. Deleniti,
    sequi. Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nisi, architecto. Architecto atque, impedit explicabo magni fuga ipsum dolore sapiente facilis, aut ea tempore nemo voluptatum. 1.
    Lorem ipsum dolor sit amet consectetur adipisicing elit. Saepe, voluptatem? 2. Lorem ipsum dolor sit amet consectetur adipisicing elit. 3. Lorem ipsum dolor sit amet consectetur adipisicing elit.
    Repudiandae vel quasi optio repellendus. Excepturi ullam ad tempora libero quidem.

    {{--
        @component('mail::button', ['url' => ''])
        Button Text
        @endcomponent
    --}}

    Terima kasih,
    <br />
    {{ config('app.name') }}
@endcomponent
