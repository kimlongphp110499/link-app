@extends('adminlte::page')

@section('title', 'Page Setting')
@push('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<style>
    /* Ẩn tất cả nội dung tab mặc định */
    .tab-pane {
        display: none;
    }
    /* Hiển thị tab khi được chọn */
    .tab-pane.active {
        display: block;
    }
</style>
@endpush

@section('content_header')
  <h1>Page Setting</h1>
@stop

@section('content')
    <body class="bg-gray-100">
        <div class="container mx-auto p-4">
            <!-- Tab navigation -->
            <div class="border-b border-gray-200">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500">
                    <li class="mr-2">
                        <button class="tab-button inline-flex p-4 rounded-t-lg border-b-2 border-gray-300 text-gray-600 active" data-tab="policy" aria-selected="true">
                            Policy
                        </button>
                    </li>
                    <li class="mr-2">
                        <button class="tab-button inline-flex p-4 rounded-t-lg border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300" data-tab="term" aria-selected="false">
                            Term
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab content -->
            <div class="tab-content mt-4">
                <!-- Policy Tab -->
                <div id="policy" class="tab-pane active">
                    <form action="{{ route('admin.policy.update') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <textarea name="content" id="editor">{!! $content !!}</textarea>
                        </div>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save</button>
                        <a href="{{ route('policy.show') }}" class="ml-2 text-gray-500 hover:underline">Cancel</a>
                    </form>
                </div>

                <!-- Term Tab -->
                <div id="term" class="tab-pane">
                    <p>Content</p>
                </div>
            </div>
        </div>
    </body>
@stop

@section('js')
<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>

<script>
    // Tab navigation logic
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');

    // Xử lý sự kiện khi nhấn vào tab
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tab = button.getAttribute('data-tab');

            // Xóa trạng thái 'active' khỏi tất cả các nút và nội dung tab
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'text-gray-600', 'border-gray-300');
                btn.classList.add('hover:text-gray-600', 'hover:border-gray-300');
                btn.setAttribute('aria-selected', 'false');
            });
            tabPanes.forEach(pane => pane.classList.remove('active'));

            // Thêm trạng thái 'active' vào nút và nội dung tab được chọn
            button.classList.add('active', 'text-gray-600', 'border-gray-300');
            button.classList.remove('hover:text-gray-600', 'hover:border-gray-300');
            button.setAttribute('aria-selected', 'true');
            document.getElementById(tab).classList.add('active');
        });
    });

    // Hiển thị tab Policy mặc định khi tải trang
    document.querySelector('[data-tab="policy"]').click();

    // Khởi tạo CKEditor
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    ClassicEditor
        .create(document.querySelector('#editor'), {
            ckfinder: {
                uploadUrl: '/ckfinder/upload?_token=' + encodeURIComponent(token)
            },
            toolbar: [
                'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                'outdent', 'indent', '|', 'imageUpload', 'blockQuote', 'insertTable', 'mediaEmbed', 'undo', 'redo'
            ]
        })
        .catch(error => {
            console.error(error);
        });
</script>
@stop