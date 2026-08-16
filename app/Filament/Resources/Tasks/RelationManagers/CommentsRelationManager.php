<?php

namespace App\Filament\Resources\Tasks\RelationManagers;

use App\Models\Comment;
use App\Services\CommentNotifier;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

/**
 * تُستخدَم على المهام وجميع وحدات الأرشيف السبع (نفس اسم العلاقة "comments"
 * في كل نموذج عبر HasComments)، لذا صنف واحد مشترك يكفي بدل تكرار صنف لكل
 * وحدة.
 */
class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'التعليقات';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('body')
                    ->label('التعليق')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->columns([
                TextColumn::make('author.name')
                    ->label('الكاتب'),
                TextColumn::make('body')
                    ->label('التعليق')
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('إضافة تعليق')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();

                        return $data;
                    })
                    ->after(fn (Comment $record) => app(CommentNotifier::class)->notifyNewComment($record)),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (Comment $record): bool => $record->canBeManagedBy(Filament::auth()->user())),
                DeleteAction::make()
                    ->visible(fn (Comment $record): bool => $record->canBeManagedBy(Filament::auth()->user())),
            ])
            ->toolbarActions([])
            ->emptyStateHeading('لا توجد تعليقات')
            ->emptyStateDescription('ابدأ النقاش حول هذا السجل.');
    }
}
