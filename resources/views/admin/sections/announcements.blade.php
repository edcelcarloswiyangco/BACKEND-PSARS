<div class="card announcement-tabs">
    <div class="announcement-tab-panels">
        <div id="announcement-create-panel" class="tab-panel">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px">
                <div>
                    <strong>Post a new announcement</strong>
                </div>
            </div>

            <form class="announcement-form" method="POST" action="{{ route('admin.announcements.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="field">
                    <label for="announcement-title">Title</label>
                    <input id="announcement-title" name="title" type="text" maxlength="255" required placeholder="Important update for pet owners">
                </div>

                <div class="field">
                    <label for="announcement-description">Description</label>
                    <textarea id="announcement-description" name="description" required placeholder="Write the full announcement here."></textarea>
                </div>

                <div class="field">
                    <label for="announcement-image">Image</label>
                    <input id="announcement-image" name="image" type="file" accept="image/*" data-image-preview-input="create">
                </div>

                <div id="announcement-create-preview" class="announcement-preview" aria-live="polite">
                    <img alt="Selected announcement preview" hidden>
                    <div class="muted">Image preview will appear here before publishing.</div>
                </div>

                <label class="checkbox-row">
                    <input type="checkbox" name="is_published" value="1" checked>
                    <span>Publish immediately</span>
                </label>

                <div>
                    <button class="btn" type="submit">Publish Announcement</button>
                </div>
            </form>
        </div>

        <div id="announcement-feed-panel" class="tab-panel hidden">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px">
                <div>
                    <strong>Announcement feed</strong>
                    <div class="muted">Manage the posts currently shown to users.</div>
                </div>
                <div class="muted">{{ ($announcements ?? collect())->count() }} total posts</div>
            </div>

            @if (($announcements ?? collect())->isEmpty())
                <div class="muted">No announcements have been posted yet.</div>
            @else
                <div class="announcement-grid">
                    @foreach ($announcements as $announcement)
                        <article class="announcement-card">
                            @if ($announcement->image_path)
                                <img src="{{ route('api.media', ['path' => $announcement->image_path]) }}" alt="{{ $announcement->title }}">
                            @endif
                            <div class="announcement-body">
                                <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start">
                                    <div>
                                        <h4 style="margin:0 0 6px 0">{{ $announcement->title }}</h4>
                                        <div class="muted">{{ $announcement->description }}</div>
                                    </div>
                                </div>
                                <div class="announcement-meta">
                                    <span>{{ optional($announcement->published_at ?? $announcement->created_at)->format('M d, Y h:i A') }}</span>
                                    <span>{{ $announcement->is_published ? 'Published' : 'Draft' }}</span>
                                </div>
                                <div class="announcement-actions">
                                    <button
                                        class="btn-ghost"
                                        type="button"
                                        data-announcement-edit-open="{{ $announcement->id }}"
                                        data-announcement-title="{{ e($announcement->title) }}"
                                        data-announcement-description="{{ e($announcement->description) }}"
                                        data-announcement-image="{{ $announcement->image_path ? route('api.media', ['path' => $announcement->image_path]) : '' }}"
                                        data-announcement-published="{{ $announcement->is_published ? 1 : 0 }}"
                                        data-announcement-update-url="{{ route('admin.announcements.update', $announcement) }}"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        class="btn-ghost"
                                        type="button"
                                        style="color:var(--danger);border-color:#fecaca"
                                        data-announcement-delete-open="{{ $announcement->id }}"
                                        data-announcement-delete-url="{{ route('admin.announcements.destroy', $announcement) }}"
                                        data-announcement-delete-title="{{ e($announcement->title) }}"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
