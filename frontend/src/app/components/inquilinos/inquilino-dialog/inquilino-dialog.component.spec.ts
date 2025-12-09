import { ComponentFixture, TestBed } from '@angular/core/testing';

import { InquilinoDialogComponent } from './inquilino-dialog.component';

describe('InquilinoDialogComponent', () => {
  let component: InquilinoDialogComponent;
  let fixture: ComponentFixture<InquilinoDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [InquilinoDialogComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(InquilinoDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
