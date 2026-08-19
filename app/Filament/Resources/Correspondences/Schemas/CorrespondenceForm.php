<?php

namespace App\Filament\Resources\Correspondences\Schemas;

use App\Enums\CorrespondenceDirection;
use App\Enums\CorrespondenceStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CorrespondenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المعاملة')
                    ->icon(Heroicon::DocumentText)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('direction')
                                ->label('نوع المعاملة')
                                ->options(CorrespondenceDirection::class)
                                ->required()
                                ->columnSpan(1),
                            TextInput::make('reference_number')
                                ->label('الرقم المرجعي')
                                ->placeholder('يُولَّد تلقائياً عند الترك فارغاً')
                                ->unique(ignoreRecord: true)
                                ->maxLength(50)
                                ->columnSpan(1),
                            TextInput::make('subject')
                                ->label('الموضوع')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                            Select::make('status')
                                ->label('الحالة')
                                ->options(CorrespondenceStatus::class)
                                ->default(CorrespondenceStatus::New)
                                ->required()
                                ->columnSpan(1),
                            DatePicker::make('document_date')
                                ->label('تاريخ الملف')
                                ->required()
                                ->maxDate(now())
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('الأطراف')
                    ->icon(Heroicon::UserGroup)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('sender_user_id')
                                ->label('من')
                                ->relationship('senderUser', 'name')
                                ->searchable()
                                ->preload()
                                ->columnSpan(1),
                            Select::make('recipient_user_id')
                                ->label('إلى')
                                ->relationship('recipientUser', 'name')
                                ->searchable()
                                ->preload()
                                ->columnSpan(1),
                            Select::make('entity_id')
                                ->label('الجهة')
                                ->relationship('entity', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->label('اسم الجهة')
                                        ->required()
                                        ->maxLength(255)
                                        ->unique('entities', 'name'),
                                ])
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('الملف والملاحظات')
                    ->icon(Heroicon::PaperClip)
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('ملف المعاملة (PDF)')
                            ->disk(config('filesystems.default'))
                            ->directory('correspondence-files')
                            ->acceptedFileTypes(['application/pdf'])
                            ->rule('extensions:pdf')
                            ->maxSize(10240)
                            ->downloadable()
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
