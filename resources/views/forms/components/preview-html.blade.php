@php
    use League\CommonMark\GithubFlavoredMarkdownConverter as Converter;
    $data = $this->form->getRawState();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    {!! (new Converter())->convert($data["content"] ?? '')->getContent() !!}
</x-dynamic-component>
